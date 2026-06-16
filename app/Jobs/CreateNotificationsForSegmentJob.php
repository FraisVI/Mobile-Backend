<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Segment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Создаёт записи в таблице notification для всех пользователей сегмента после отправки по топику.
 */
class CreateNotificationsForSegmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        public int $segmentId,
        public string $title,
        public ?int $articleId,
        public ?string $label,
        public ?string $color
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        try {
            $segment = Segment::getElement($this->segmentId);
            if (!$segment) {
                Log::channel('firebase')->warning('CreateNotificationsForSegmentJob: segment not found', [
                    'segment_id' => $this->segmentId,
                ]);
                return;
            }

            $userIds = $segment->getMemberUserIds();
            if ($userIds->isEmpty()) {
                Log::channel('firebase')->info('CreateNotificationsForSegmentJob: segment has no members', [
                    'segment_id' => $this->segmentId,
                ]);
                return;
            }

            $now = now();
            $rows = [];
            $totalInserted = 0;

            foreach ($userIds->chunk(1000) as $chunkIndex => $chunk) {
                foreach ($chunk as $userId) {
                    $rows[] = [
                        'user_id' => $userId,
                        'article_id' => $this->articleId,
                        'rate_hash' => null,
                        'message' => $this->title,
                        'label' => $this->label ?? '',
                        'color' => $this->color ?? '',
                        'created_at' => $now,
                    ];
                }

                if (count($rows) >= 1000) {
                    try {
                        DB::table('notification')->insert($rows);
                        $totalInserted += count($rows);
                        $rows = [];
                    } catch (\Throwable $e) {
                        Log::channel('firebase')->error('CreateNotificationsForSegmentJob: insert failed', [
                            'segment_id' => $this->segmentId,
                            'chunk' => $chunkIndex,
                            'rows_count' => count($rows),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e;
                    }
                }
            }

            if (!empty($rows)) {
                try {
                    DB::table('notification')->insert($rows);
                    $totalInserted += count($rows);
                } catch (\Throwable $e) {
                    Log::channel('firebase')->error('CreateNotificationsForSegmentJob: final insert failed', [
                        'segment_id' => $this->segmentId,
                        'rows_count' => count($rows),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }
            }

            Log::channel('firebase')->info('Notifications created for segment', [
                'segment_id' => $this->segmentId,
                'users_count' => $userIds->count(),
                'notifications_inserted' => $totalInserted,
            ]);
        } catch (\Throwable $e) {
            Log::channel('firebase')->error('CreateNotificationsForSegmentJob: unexpected error', [
                'segment_id' => $this->segmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
