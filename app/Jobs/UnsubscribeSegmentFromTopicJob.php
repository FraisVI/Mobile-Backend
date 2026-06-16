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
 * Отписывает участников сегмента от Firebase-топика перед удалением сегмента.
 * После выполнения в цепочке может запускаться DeleteSegmentAfterUnsubscribeJob (при удалении).
 */
class UnsubscribeSegmentFromTopicJob implements ShouldQueue
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
        if (!$segment) {
            return;
        }

        $topic = $segment->firebase_topic ?? null;
        if (empty($topic)) {
            Log::channel('firebase')->info('Segment has no topic, skip unsubscribe', [
                'segment_id' => $this->segmentId,
            ]);
            return;
        }

        try {
            $userIds = $segment->getMemberUserIds();
            if ($userIds->isEmpty()) {
                Log::channel('firebase')->info('Segment has no members, skip unsubscribe', [
                    'segment_id' => $this->segmentId,
                    'topic' => $topic,
                ]);
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
                Log::channel('firebase')->info('Segment members have no FCM tokens, skip unsubscribe', [
                    'segment_id' => $this->segmentId,
                    'topic' => $topic,
                ]);
                return;
            }

            /** @var Messaging $messaging */
            $messaging = app('firebase.messaging');
            $chunks = array_chunk($tokens, self::CHUNK_SIZE);

            foreach ($chunks as $chunk) {
                $messaging->unsubscribeFromTopic($topic, $chunk);
            }

            Log::channel('firebase')->info('Segment topic unsubscription completed', [
                'segment_id' => $this->segmentId,
                'topic' => $topic,
                'tokens_count' => count($tokens),
            ]);
        } catch (FirebaseException $e) {
            Log::channel('firebase')->error('Segment topic unsubscription failed', [
                'segment_id' => $this->segmentId,
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::channel('firebase')->error('Segment topic unsubscription error', [
                'segment_id' => $this->segmentId,
                'topic' => $topic ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
