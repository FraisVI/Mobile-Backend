<?php

namespace App\Http\Controllers\ServiceApi;

use App\Jobs\SubscribeSegmentToTopicJob;
use App\Models\Segment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SegmentController extends ServiceApiController
{
    const LOG_CHANNEL = 'service-api';

    public function push(Request $request): JsonResponse
    {
        $data = (object) $request->json()->all();

        if (!isset($data->segments))
            return response()->json(['message' => 'segments field is missing'], 422);

        foreach ($data->segments as $s) {
            $s = (object) $s;

            $segment = Segment::where('uuid', $s->id)->first();
            $isNewSegment = !$segment;
            if ($isNewSegment) {
                $segment = new Segment();
            }

            $segment->uuid = $s->id;
            $segment->name = $s->name;
            $segment->card_ids = $s->card_ids ?? [];

            if (isset($s->firebase_topic) && !empty($s->firebase_topic)) {
                $segment->firebase_topic = $this->sanitizeTopicName($s->firebase_topic);
            } elseif (empty($segment->firebase_topic)) {
                if ($isNewSegment) {
                    $segment->save();
                    $segment->firebase_topic = 'topic_' . $segment->id;
                } else {
                    $segment->firebase_topic = 'topic_' . $segment->id;
                }
            }

            $segment->save();

            $cardIds = is_array($s->card_ids ?? null) ? $s->card_ids : (array) ($s->card_ids ?? []);
            $segment->syncMembersFromCardIds($cardIds);

            if ($segment->id > 0) {
                if (empty($segment->firebase_topic)) {
                    $segment->firebase_topic = 'topic_' . $segment->id;
                    $segment->save();
                }
                $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_PENDING;
                $segment->save();
                SubscribeSegmentToTopicJob::dispatch($segment->id);
            }
        }

        Log::channel(self::LOG_CHANNEL)->info('segment/push', [
            'ip' => $request->ip(),
            'count' => count($data->segments),
            'topics_set' => collect($data->segments)->map(fn($s) => $s->firebase_topic ?? null)->filter()->toArray()
        ]);

        return response()->json(['Segment added'], 200);
    }

    /**
     * Очищает имя топика от недопустимых символов (Firebase допускает только a-zA-Z0-9_-).
     */
    private function sanitizeTopicName(string $topicName): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $topicName);

        if (empty($sanitized)) {
            return 'topic_' . time();
        }

        return $sanitized;
    }
}
