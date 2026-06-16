<?php

namespace App\Http\Controllers\ServiceApi;

use App\Models\AppUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChangePhoneController extends ServiceApiController
{
    const LOG_CHANNEL = 'service-api';

    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $clientCardId = $request->get('client_card_id');
            $currentPhone = $request->get('current_phone');
            $newPhone     = $request->get('new_phone');

            if (!$clientCardId || !$currentPhone || !$newPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Отсутствуют обязательные параметры',
                ], 400);
            }

            if (!preg_match('/^\d{10}$/', $currentPhone) || !preg_match('/^\d{10}$/', $newPhone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный формат номера телефона',
                ], 400);
            }

            $user = AppUser::where('client_card_id', $clientCardId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден',
                ], 404);
            }

            if ($user->phone !== $currentPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Текущий номер не совпадает',
                ], 400);
            }

            $user->phone = $newPhone;
            $user->updated_at = Carbon::now();
            $user->save();

            Log::channel(self::LOG_CHANNEL)->info('Phone changed', [
                'client_card_id' => $clientCardId,
                'old_phone' => $currentPhone,
                'new_phone' => $newPhone,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Номер телефона обновлён',
                'data' => [
                    'client_card_id' => $user->client_card_id,
                    'old_phone' => $currentPhone,
                    'new_phone' => $newPhone,
                    'updated_at' => $user->updated_at->format(self::DATE_TIME_FORMAT),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->error('ChangePhoneController error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении номера',
            ], 500);
        }
    }
}
