<?php

namespace App\Services;

use App\Models\Segment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Подписка/отписка FCM-токенов на топики (SendAll и топики сегментов пользователя).
 */
class FcmSubscriptionService
{
    private const LOG_CHANNEL = 'profile';

    public static function subscribeTokenToAllTopics(int $userId, string $token): void
    {
        $messaging = app('firebase.messaging');

        try {
            $messaging->subscribeToTopics([Segment::TOPIC_SEND_ALL], [$token]);
            Log::channel(self::LOG_CHANNEL)->info('Subscribed to SendAll', ['user_id' => $userId]);
        } catch (\Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning('SendAll subscription failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        if (!Schema::hasTable('segment_user')) {
            return;
        }

        $segmentIds = DB::table('segment_user')->where('user_id', $userId)->pluck('segment_id');
        $topics = Segment::whereIn('id', $segmentIds)
            ->whereNotNull('firebase_topic')
            ->where('firebase_topic', '!=', '')
            ->pluck('firebase_topic')
            ->unique()
            ->values()
            ->all();

        foreach ($topics as $topic) {
            try {
                $messaging->subscribeToTopics([$topic], [$token]);
                Log::channel(self::LOG_CHANNEL)->info('Subscribed to segment topic', [
                    'user_id' => $userId,
                    'topic' => $topic,
                ]);
            } catch (\Throwable $e) {
                Log::channel(self::LOG_CHANNEL)->warning('Segment topic subscription failed', [
                    'user_id' => $userId,
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public static function unsubscribeTokenFromAllTopics(int $userId, string $token): void
    {
        $messaging = app('firebase.messaging');

        try {
            $messaging->unsubscribeFromTopic(Segment::TOPIC_SEND_ALL, [$token]);
            Log::channel(self::LOG_CHANNEL)->info('Unsubscribed token from SendAll', ['user_id' => $userId]);
        } catch (\Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->debug('SendAll unsubscribe failed (ignored)', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        if (!Schema::hasTable('segment_user')) {
            return;
        }

        $segmentIds = DB::table('segment_user')->where('user_id', $userId)->pluck('segment_id');
        $topics = Segment::whereIn('id', $segmentIds)
            ->whereNotNull('firebase_topic')
            ->where('firebase_topic', '!=', '')
            ->pluck('firebase_topic')
            ->unique()
            ->values()
            ->all();

        foreach ($topics as $topic) {
            try {
                $messaging->unsubscribeFromTopic($topic, [$token]);
            } catch (\Throwable $e) {
                Log::channel(self::LOG_CHANNEL)->debug('Segment topic unsubscribe failed (ignored)', [
                    'user_id' => $userId,
                    'topic' => $topic,
                ]);
            }
        }
    }
}
