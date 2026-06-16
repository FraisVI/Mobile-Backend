<?php

namespace App\Jobs;

use App\Support\FirebaseLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFirebaseNotificationChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;

    public array $chunk;
    public string $title;
    public string $message;
    public string $image;
    public array $data;
    public bool $priority;

    const LOG_CHANNEL = 'firebase';

    public function __construct(array $chunk, string $title, string $message, string $image = '', array $data = [], bool $priority = false)
    {
        $this->chunk = $chunk;
        $this->title = $title;
        $this->message = $message;
        $this->image = $image;
        $this->data = $data;
        $this->priority = $priority;
    }

    public function handle(): void
    {
        $token = app(\App\FirebaseNotificationService::class)->getAccessToken();

        foreach ($this->chunk as $deviceToken) {

            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $this->title,
                        'body'  => $this->message,
                        'image' => $this->image ?: null,
                    ],
                    'data' => array_map('strval', $this->data),
                    'android' => [
                        'priority' => $this->priority ? 'high' : 'normal',
                        'notification' => [
                            'image' => $this->image ?: null,
                        ],
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => $this->priority ? '10' : '5',
                        ],
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $this->title,
                                    'body'  => $this->message,
                                ],
                                'sound' => 'default',
                                'badge' => 1,
                                'mutable-content' => 1,
                            ],
                        ],
                        'fcm_options' => [
                            'image' => $this->image,
                        ],
                    ],
                ],
            ];

            if (empty($this->image)) {
                unset($payload['message']['notification']['image']);
                unset($payload['message']['android']['notification']['image']);
                unset($payload['message']['apns']['fcm_options']);
            } else {
                $payload['message']['apns']['fcm_options'] = ['image' => $this->image];
            }

            try {
                $response = Http::withToken($token)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(30)
                    ->post('https://fcm.googleapis.com/v1/projects/SOME_ID/messages:send', $payload);

                $body = $response->json() ?? $response->body();

                if ($response->successful()) {
                    FirebaseLogger::info('Firebase push sent successfully', [
                        'token'   => substr($deviceToken, 0, 10) . '...',
                        'status'  => $response->status(),
                        'body'    => $body,
                    ]);
                } else {
                    FirebaseLogger::warning('Firebase push failed', [
                        'token'   => substr($deviceToken, 0, 10) . '...',
                        'status'  => $response->status(),
                        'body'    => $body,
                    ]);

                    if (isset($body['error']['status']) && in_array($body['error']['status'], [
                            'UNREGISTERED',
                            'INVALID_ARGUMENT',
                            'NOT_FOUND'
                        ])) {

                        $session = \App\Models\Session::where('fcm_token', $deviceToken)->first();
                        if ($session) {
                            $session->fcm_token = null;
                            $session->save();
                        }

                        FirebaseLogger::error("FCM token removed: {$deviceToken}", [
                            'reason' => $body['error']['status'],
                        ]);

                        $this->chunk = array_filter($this->chunk, fn($t) => $t !== $deviceToken);
                    }

                    if ($response->status() === 401) {
                        \Illuminate\Support\Facades\Cache::forget('firebase_access_token');
                    }
                }
            } catch (\Throwable $e) {
                FirebaseLogger::critical('Exception while sending Firebase push', [
                    'token'   => substr($deviceToken, 0, 10) . '...',
                    'error'   => $e->getMessage(),
                    'payload' => $payload,
                    'trace'   => $e->getTraceAsString(),
                ]);

                if (strpos($e->getMessage(), '401') !== false) {
                    \Illuminate\Support\Facades\Cache::forget('firebase_access_token');
                }
            }
        }
    }

    public static function sendSingle(string $deviceToken, string $title, string $message, string $image = '', array $data = []): bool
    {
        $token = app(\App\FirebaseNotificationService::class)->getAccessToken();

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $message,
                    'image' => $image ?: null,
                ],
                'data' => array_map('strval', $data),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'title' => $title,
                        'body'  => $message,
                        'image' => $image ?: null,
                        'sound' => 'default',
                        'channel_id' => 'high_priority',
                        'notification_priority' => 'PRIORITY_MAX',
                        'visibility' => 'PUBLIC',
                        'default_sound' => true,
                        'sticky' => false,
                        'vibrate_timings_millis' => [100, 200, 100, 200],
                    ],
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
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
                            'interruption-level' => 'time-sensitive',
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
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(15)
                ->post('https://fcm.googleapis.com/v1/projects/SOME_ID/messages:send', $payload);

            $body = $response->json() ?? $response->body();

            if ($response->successful()) {
                FirebaseLogger::info('High-priority Firebase push sent successfully', [
                    'token'  => substr($deviceToken, 0, 10) . '...',
                    'body'   => $body,
                ]);
                return true;
            }

            FirebaseLogger::warning('High-priority Firebase push failed', [
                'token'  => substr($deviceToken, 0, 10) . '...',
                'status' => $response->status(),
                'body'   => $body,
            ]);

            if ($response->status() === 401) {
                \Illuminate\Support\Facades\Cache::forget('firebase_access_token');
            }

            if (isset($body['error']['status']) && in_array($body['error']['status'], [
                    'UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'
                ])) {
                \App\Models\Session::where('fcm_token', $deviceToken)->update(['fcm_token' => null]);
            }

        } catch (\Throwable $e) {
            FirebaseLogger::critical('Exception while sending high-priority Firebase push', [
                'token'   => substr($deviceToken, 0, 10) . '...',
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if (strpos($e->getMessage(), '401') !== false) {
                \Illuminate\Support\Facades\Cache::forget('firebase_access_token');
            }
        }

        return false;
    }
}
