<?php

namespace App\Http\Controllers\ServiceApi;

use App\Models\AppUser;
use App\Models\Session;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\FirebaseNotificationService;

class NotificationController extends ServiceApiController
{
    const LOG_CHANNEL = 'service-api';

    protected FirebaseNotificationService $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function send(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'clientcardid' => ['required', 'string'],
            'message' => ['required', 'string'],
            'type' => ['required', Rule::in(['promo', 'warning'])],
        ]);
        $validator->validate();

        $clientCardId = $data['clientcardid'];
        $message = $data['message'];
        $type = $data['type'];
        $title = 'Сервисное сообщение';

        $user = AppUser::where('client_card_id', $clientCardId)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->message = $message;
        $notification->article_id = null;
        $notification->rate_hash = null;

        if ($type === 'promo') {
            $notification->label = 'Промо';
            $notification->color = 'primary';
        } elseif ($type === 'warning') {
            $notification->label = 'Внимание';
            $notification->color = 'warning';
        }

        $notification->save();

        $tokens = Session::where('user_id', $user->id)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->unique()
            ->filter(fn($token) => is_string($token) && strlen($token) > 0)
            ->values()
            ->all();

        if (!empty($tokens)) {
            try {
                $result = $this->firebaseService->sendNotificationBatch(
                    500,
                    $tokens,
                    $title,
                    $message,
                    '',
                    ['article' => $type],
                    true
                );
            } catch (\Throwable $e) {
                Log::channel(self::LOG_CHANNEL)->error('FCM error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json(['error' => 'Push sending failed'], 500);
            }
        } else {
            Log::channel(self::LOG_CHANNEL)->warning('No FCM tokens found for user', [
                'client_card_id' => $clientCardId,
            ]);
        }

        Log::channel(self::LOG_CHANNEL)->info('notification/send', [
            'ip' => $request->ip(),
            'client_card_id' => $clientCardId,
            'message' => $message,
            'type' => $type,
            'token_count' => count($tokens),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
