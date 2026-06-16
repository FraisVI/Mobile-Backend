<?php

namespace App\Jobs;

use App\Models\Segment;
use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;

/**
 * Подписывает участников сегмента на Firebase-топик сегмента (очередь, без нагрузки на запрос).
 */
class SubscribeSegmentToTopicJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 600;

    private const CHUNK_SIZE = 1000;

    public function __construct(
        public int $segmentId
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        if ($this->segmentId === Segment::SEGMENT_ID_ALL_USERS) {
            return;
        }

        $segment = Segment::find($this->segmentId);
        if (!$segment || empty($segment->firebase_topic)) {
            return;
        }

        $topic = $segment->firebase_topic;

        $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_IN_PROGRESS;
        $segment->save();

        try {
            $userIds = $segment->getMemberUserIds();
            if ($userIds->isEmpty()) {
                $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_COMPLETED;
                $segment->save();
                return;
            }

            $tokens = Session::query()
                ->whereIn('user_id', $userIds)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->pluck('fcm_token')
                ->unique()
                ->values()
                ->all();

            if (empty($tokens)) {
                $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_COMPLETED;
                $segment->save();
                return;
            }

            /** @var Messaging $messaging */
            $messaging = app('firebase.messaging');
            $chunks = array_chunk($tokens, self::CHUNK_SIZE);

            foreach ($chunks as $chunk) {
                $messaging->subscribeToTopics([$topic], $chunk);
            }

            $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_COMPLETED;
            $segment->save();

            Log::channel('firebase')->info('Segment topic subscription completed', [
                'segment_id' => $this->segmentId,
                'topic' => $topic,
                'tokens_count' => count($tokens),
            ]);
        } catch (FirebaseException $e) {
            Log::channel('firebase')->error('Segment topic subscription failed', [
                'segment_id' => $this->segmentId,
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_FAILED;
            $segment->save();
        } catch (\Throwable $e) {
            Log::channel('firebase')->error('Segment topic subscription error', [
                'segment_id' => $this->segmentId,
                'topic' => $topic ?? null,
                'error' => $e->getMessage(),
            ]);
            $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_FAILED;
            $segment->save();
            throw $e;
        }
    }
}
