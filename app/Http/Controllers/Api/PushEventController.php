<?php

namespace App\Http\Controllers\Api;

use App\Jobs\RecordPushAnalyticsEventJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Приложение вызывает этот endpoint при событиях по пушу: доставлено, открыто, переход.
 * Запись в БД выполняется через очередь, чтобы не нагружать API при большом числе событий.
 * Аналитика учитывается только в течение 3 дней после отправки рассылки.
 */
class PushEventController extends ApiController
{
    private const ANALYTICS_DAYS = 3;

    public function store(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'send_id' => 'required|integer|min:1',
            'event'   => 'required|string|in:delivered,opened,click',
        ], [
            'send_id.required' => 'send_id обязателен',
            'event.in'         => 'event: delivered, opened или click',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $sendId = (int) $data['send_id'];
        $event = $data['event'];
        $userId = $this->session->user_id ?? 0;
        if ($userId < 1) {
            return response()->json(['message' => 'Пользователь не определён'], 401);
        }

        $log = DB::table('notification_sent_log')->where('id', $sendId)->first();
        if (!$log) {
            return response()->json(['message' => 'Рассылка не найдена'], 404);
        }

        $sendTime = \Carbon\Carbon::parse($log->time);
        $cutoff = $sendTime->copy()->addDays(self::ANALYTICS_DAYS);
        if (now()->gt($cutoff)) {
            return response()->json(['message' => 'Сбор аналитики по этой рассылке завершён'], 400);
        }

        RecordPushAnalyticsEventJob::dispatch($sendId, $userId, $event);

        return response()->json(['status' => 'ok']);
    }
}
