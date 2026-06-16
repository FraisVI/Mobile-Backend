<?php

namespace App;

use Google\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendFirebaseNotificationChunk;
use App\Support\FirebaseLogger;

class FirebaseNotificationService
{
    private string $endpoint = 'https://fcm.googleapis.com/v1/projects/SOME_ID/messages:send';
    private const LOG_CHANNEL = 'firebase';

    public function getAccessToken(): string
    {
        FirebaseLogger::info('Requesting Firebase access token');

        return Cache::remember('firebase_access_token', now()->addMinutes(50), function () {
            try {
                $client = new Client();
                $client->setAuthConfig(storage_path('app/firebase/service-account.json'));
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

                $token = $client->fetchAccessTokenWithAssertion()['access_token'] ?? null;

                if (!$token) {
                    FirebaseLogger::error('Access token not received');
                    throw new \RuntimeException('Access token generation failed');
                }

                FirebaseLogger::info('Firebase access token generated successfully');
                return $token;
            } catch (\Throwable $e) {
                FirebaseLogger::error('Failed to generate access token', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });
    }

    public function sendNotificationBatch(
        int $tokenLimit,
        array $tokens,
        string $title,
        string $message,
        string $image = '',
        array $data = [],
        bool $priority = false
    ): array {
        $tokens = array_unique(array_filter($tokens));
        $chunks = array_chunk($tokens, $tokenLimit);

        FirebaseLogger::info('Starting Firebase batch dispatch', [
            'total_tokens' => count($tokens),
            'chunks' => count($chunks),
            'priority' => $priority,
        ]);

        foreach ($chunks as $index => $chunk) {
            if (empty($chunk)) {
                FirebaseLogger::warning("Chunk #{$index} is empty, skipped");
                continue;
            }

            try {
                SendFirebaseNotificationChunk::dispatch(
                    $chunk,
                    $title,
                    $message,
                    $image,
                    $data,
                    $priority
                );
                FirebaseLogger::info("Dispatched Firebase chunk #{$index}", [
                    'count' => count($chunk),
                ]);
            } catch (\Throwable $e) {
                FirebaseLogger::error("Failed to dispatch Firebase chunk #{$index}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        FirebaseLogger::info('Firebase batch dispatch complete', [
            'total_tokens' => count($tokens),
        ]);

        return ['tokens_total' => count($tokens)];
    }

    /**
     * Отправка уведомления на Firebase Topic (один запрос, без загрузки токенов).
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $message,
        string $image = '',
        array $data = [],
        bool $priority = false
    ): array {
        $token = $this->getAccessToken();

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body'  => $message,
                    'image' => $image ?: null,
                ],
                'data' => array_map('strval', $data),
                'android' => [
                    'priority' => $priority ? 'high' : 'normal',
                    'notification' => [
                        'image' => $image ?: null,
                    ],
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => $priority ? '10' : '5',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body'  => $message,
                            ],
                            'sound' => 'default',
                            'badge' => 1,
                            'mutable-content' => 1,
                        ],
                    ],
                    'fcm_options' => [
                        'image' => $image,
                    ],
                ],
            ],
        ];

        if (empty($image)) {
            unset($payload['message']['notification']['image']);
            unset($payload['message']['android']['notification']['image']);
            unset($payload['message']['apns']['fcm_options']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->post($this->endpoint, $payload);

            $status = $response->status();
            $body = $response->json() ?? $response->body();

            if ($response->successful()) {
                FirebaseLogger::info('Firebase topic message sent', [
                    'topic' => $topic,
                    'priority' => $priority,
                ]);
                return ['topic' => $topic, 'success' => true, 'status' => $status, 'response' => $body];
            }

            FirebaseLogger::warning('Firebase topic send failed', [
                'topic' => $topic,
                'status' => $status,
                'body' => $body,
            ]);
            if ($status === 401) {
                Cache::forget('firebase_access_token');
            }
            return ['topic' => $topic, 'success' => false, 'status' => $status, 'response' => $body];
        } catch (\Throwable $e) {
            FirebaseLogger::error('Firebase topic send exception', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (strpos($e->getMessage(), '401') !== false) {
                Cache::forget('firebase_access_token');
            }
            throw $e;
        }
    }

    public function validateTokens(array $tokens): array
    {
        FirebaseLogger::info('Starting FCM token validation', [
            'tokens_total' => count($tokens),
        ]);

        $token = $this->getAccessToken();
        $invalidTokens = [];

        foreach ($tokens as $deviceToken) {
            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => 'Validation',
                        'body' => 'Token validation check',
                    ],
                ],
            ];

            try {
                $response = Http::withToken($token)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(15)
                    ->post($this->endpoint, $payload);

                if (!$response->successful()) {
                    $body = $response->json() ?? ['error' => 'No response body'];

                    if (isset($body['error']['status']) && in_array($body['error']['status'], ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'])) {
                        $invalidTokens[] = $deviceToken;
                        FirebaseLogger::warning('Invalid FCM token detected', [
                            'token' => substr($deviceToken, 0, 10) . '...',
                            'reason' => $body['error']['status'],
                        ]);
                    }
                } else {
                    FirebaseLogger::info('Valid FCM token confirmed', [
                        'token' => substr($deviceToken, 0, 10) . '...',
                    ]);
                }
            } catch (\Throwable $e) {
                FirebaseLogger::error('Error during FCM token validation', [
                    'token' => substr($deviceToken, 0, 10) . '...',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                if (strpos($e->getMessage(), '401') !== false) {
                    Cache::forget('firebase_access_token');
                    FirebaseLogger::warning('Firebase access token invalidated due to 401');
                }
            }
        }

        if (!empty($invalidTokens)) {
            \App\Models\Session::whereIn('fcm_token', $invalidTokens)->update(['fcm_token' => null]);
            FirebaseLogger::info('Invalid FCM tokens removed from database', [
                'count' => count($invalidTokens),
            ]);
        }

        FirebaseLogger::info('FCM token validation complete', [
            'valid' => count($tokens) - count($invalidTokens),
            'invalid' => count($invalidTokens),
        ]);

        return [
            'invalid_tokens' => $invalidTokens,
            'valid_tokens' => array_diff($tokens, $invalidTokens),
        ];
    }
}
