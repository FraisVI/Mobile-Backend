<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Отчёты по рассылкам. Аналитика учитывается только в течение 3 дней после отправки.
 */
class NotificationReportsController extends AdminController
{
    private const ANALYTICS_DAYS = 3;

    public function index(): View
    {
        $title = 'Отчёты по рассылкам';

        $logs = DB::table('notification_sent_log')
            ->where(function ($q) {
                $q->whereNotNull('topic_name')->orWhere('send_method', 'topic');
            })
            ->orderBy('time', 'desc')
            ->limit(200)
            ->get();

        $reports = [];
        $hasEventsTable = \Schema::hasTable('push_analytics_events');
        $eventCounts = [];

        if ($hasEventsTable && $logs->isNotEmpty()) {
            $sendIds = $logs->pluck('id')->all();
            $rows = DB::table('push_analytics_events as e')
                ->join('notification_sent_log as l', 'e.send_id', '=', 'l.id')
                ->whereIn('e.send_id', $sendIds)
                ->whereRaw('e.created_at <= DATE_ADD(l.time, INTERVAL ' . self::ANALYTICS_DAYS . ' DAY)')
                ->selectRaw('e.send_id, e.event_type, COUNT(DISTINCT e.user_id) as cnt')
                ->groupBy('e.send_id', 'e.event_type')
                ->get();
            foreach ($rows as $r) {
                $eventCounts[$r->send_id][$r->event_type] = (int) $r->cnt;
            }
        }

        foreach ($logs as $log) {
            $sendTime = Carbon::parse($log->time);
            $cutoff = $sendTime->copy()->addDays(self::ANALYTICS_DAYS);
            $sent = (int) ($log->sent ?? $log->total ?? 0);

            $delivered = (int) ($eventCounts[$log->id]['delivered'] ?? 0);
            $opened = (int) ($eventCounts[$log->id]['opened'] ?? 0);
            $clicks = (int) ($eventCounts[$log->id]['click'] ?? 0);

            $pctDelivered = $sent > 0 ? round($delivered * 100 / $sent, 1) : 0;
            $pctOpened = $delivered > 0 ? round($opened * 100 / $delivered, 1) : 0;
            $pctClicks = $opened > 0 ? round($clicks * 100 / $opened, 1) : 0;

            $reports[] = (object) [
                'id'             => $log->id,
                'time'           => $sendTime,
                'title'          => $log->title,
                'message'        => $log->message,
                'topic_name'     => $log->topic_name ?? '—',
                'sent'           => $sent,
                'delivered'      => $delivered,
                'opened'         => $opened,
                'clicks'         => $clicks,
                'pct_delivered'  => $pctDelivered,
                'pct_opened'     => $pctOpened,
                'pct_clicks'     => $pctClicks,
                'analytics_until' => $cutoff,
                'collecting'     => now()->lte($cutoff),
            ];
        }

        return view('admin.notification-reports', compact('title', 'reports'));
    }
}
