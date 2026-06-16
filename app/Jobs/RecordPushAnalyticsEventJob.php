<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Записывает событие аналитики по пушу в БД (в фоне, без нагрузки на API).
 */
class RecordPushAnalyticsEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 10;

    private const ANALYTICS_DAYS = 3;

    public function __construct(
        public int $sendId,
        public int $userId,
        public string $eventType
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        if (!Schema::hasTable('push_analytics_events')) {
            return;
        }

        $log = DB::table('notification_sent_log')->where('id', $this->sendId)->first();
        if (!$log) {
            return;
        }

        $sendTime = Carbon::parse($log->time);
        $cutoff = $sendTime->copy()->addDays(self::ANALYTICS_DAYS);
        if (now()->gt($cutoff)) {
            return;
        }

        DB::table('push_analytics_events')->insertOrIgnore([
            'send_id'    => $this->sendId,
            'user_id'    => $this->userId,
            'event_type' => $this->eventType,
            'created_at' => now(),
        ]);
    }
}
