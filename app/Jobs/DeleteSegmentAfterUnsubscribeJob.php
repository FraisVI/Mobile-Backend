<?php

namespace App\Jobs;

use App\Models\Segment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Удаляет сегмент после отписки от топика (запускается по цепочке после UnsubscribeSegmentFromTopicJob).
 * Очищает запись в firebase_topics и удаляет сегмент (segment_user удаляется по FK cascade).
 */
class DeleteSegmentAfterUnsubscribeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

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

        try {
            if (\Schema::hasTable('firebase_topics')) {
                $deleted = DB::table('firebase_topics')->where('segment_id', $this->segmentId)->delete();
                if ($deleted > 0) {
                    Log::channel('firebase')->info('firebase_topics cleanup', [
                        'segment_id' => $this->segmentId,
                        'rows_deleted' => $deleted,
                    ]);
                }
            }

            Segment::destroy($this->segmentId);

            Log::channel('firebase')->info('Segment deleted after unsubscribe', [
                'segment_id' => $this->segmentId,
            ]);
        } catch (\Throwable $e) {
            Log::channel('firebase')->error('Delete segment after unsubscribe failed', [
                'segment_id' => $this->segmentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
