<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseTopicController extends Controller
{
    /**
     * Подписать один/несколько токенов на topic.
     *
     * expected JSON body:
     * {
     *   "topic": "news",
     *   "tokens": "one-token" или массив ["token1","token2",...]
     * }
     */
    public function subscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'topic'  => 'required|string|regex:/^[a-zA-Z0-9_\-]+$/',
            'tokens' => 'required',
        ]);

        $topic = (string) $data['topic'];

        $tokens = is_array($data['tokens']) ? $data['tokens'] : [$data['tokens']];

        if (count($tokens) === 0) {
            return response()->json(['error' => 'tokens пусты'], 422);
        }

        $chunks = array_chunk($tokens, 1000);

        $messaging = app('firebase.messaging');

        $results = [];
        foreach ($chunks as $chunkIndex => $chunk) {
            try {
                $response = $messaging->subscribeToTopics([$topic], $chunk);
                $results[] = [
                    'chunk' => $chunkIndex,
                    'topic' => $topic,
                    'tokens' => $response[$topic] ?? [],
                ];
            } catch (FirebaseException $e) {
                Log::error('Firebase subscribe error', ['topic' => $topic, 'error' => $e->getMessage(), 'chunk' => $chunkIndex]);
                $results[] = [
                    'chunk' => $chunkIndex,
                    'error' => $e->getMessage(),
                ];
            } catch (\Throwable $e) {
                Log::error('Unexpected subscribe error', ['topic' => $topic, 'error' => $e->getMessage()]);
                $results[] = [
                    'chunk' => $chunkIndex,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'topic' => $topic,
            'chunks' => count($chunks),
            'results' => $results,
        ]);
    }

    /**
     * Отписать токены от topic (аналогично подписке).
     */
    public function unsubscribe(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'topic'  => 'required|string|regex:/^[a-zA-Z0-9_\-]+$/',
            'tokens' => 'required',
        ]);

        $topic = (string) $data['topic'];
        $tokens = is_array($data['tokens']) ? $data['tokens'] : [$data['tokens']];

        $chunks = array_chunk($tokens, 1000);
        $messaging = app('firebase.messaging');

        $results = [];
        foreach ($chunks as $i => $chunk) {
            try {
                $response = $messaging->unsubscribeFromTopic($topic, $chunk);
                $results[] = [
                    'chunk' => $i,
                    'success' => $response->successCount ?? null,
                    'failure' => $response->failureCount ?? null,
                ];
            } catch (\Throwable $e) {
                Log::error('Firebase unsubscribe error', ['topic' => $topic, 'error' => $e->getMessage()]);
                $results[] = ['chunk' => $i, 'error' => $e->getMessage()];
            }
        }

        return response()->json(['topic' => $topic, 'results' => $results]);
    }

    public function sendToTopic(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'topic'   => 'required|string|regex:/^[a-zA-Z0-9_\-]+$/',
            'title'   => 'required|string',
            'body'    => 'required|string',
            'payload' => 'array',
        ]);

        $topic = $data['topic'];

        $messaging = app('firebase.messaging');

        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('topic', $topic)
            ->withNotification([
                'title' => $data['title'],
                'body'  => $data['body'],
            ])
            ->withData($data['payload'] ?? []);

        try {
            $response = $messaging->send($message);

            return response()->json([
                'topic'    => $topic,
                'message'  => 'sent',
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'topic' => $topic,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
