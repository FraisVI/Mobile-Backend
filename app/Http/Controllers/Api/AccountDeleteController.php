<?php

namespace App\Http\Controllers\Api;

use App\CardService;
use App\Models\AccountDeletionInfo;
use App\Models\AppUser;
use App\Models\CallCode;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\Rating;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AccountDeleteController extends ApiController
{
    public static int $RETRY_TIME = 5 * 60; // 5 min
    public static int $MAX_ATTEMPTS = 10;

    const LOG_CHANNEL = 'account-delete';

    public function initiate(Request $request, CardService $cardService): \Illuminate\Http\JsonResponse
    {
        $accountInfo = AccountDeletionInfo::find($this->appUser->id);

        $now = Carbon::now();
        if (!$accountInfo || $accountInfo->date_sent->diffInRealSeconds($now) > self::$RETRY_TIME) {
            if (!$accountInfo) {
                $accountInfo = new AccountDeletionInfo();
                $accountInfo->user_id = $this->appUser->id;
            }
            $accountInfo->date_sent = $now;
            $accountInfo->fail_count = 0;
            $accountInfo->save();

            try {
                $cardService->InitiateAccountDeletion(['clientcardid' => $this->appUser->client_card_id]);
            } catch (\Exception $e) {
                return $this->ResponseGenericError($e->getMessage());
            }

            Log::channel(self::LOG_CHANNEL)->info('initiate', [
                'ip' => $request->ip(),
                'appuser_id' => $this->appUser->id,
                'client_card_id' => $this->appUser->client_card_id,
                'phone' => $this->appUser->phone
            ]);
        }

        return response()->json();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function confirm(Request $request, CardService $cardService): \Illuminate\Http\JsonResponse
    {
        $data = (object) $request->json()->all();
        $validator = Validator::make($request->json()->all(), [
            'code' => 'required|regex:/^[0-9]{4}$/'
        ], $this->messages);
        $validator->validate();

        $accountInfo = AccountDeletionInfo::find($this->appUser->id);
        if (!$accountInfo) {
            return $this->ResponseGenericError('Не удалось подтвердить удаление аккаунта. Возможно процесс уже завёршен или произошла ошибка.');
        }

        if ($accountInfo->fail_count > self::$MAX_ATTEMPTS) {
            return $this->ResponseGenericError('Превышено максимальное кол-во попыток на ввод кода. Повторите попытку позже.');
        }

        if ($accountInfo->date_sent->diffInRealSeconds(Carbon::now()) > self::$RETRY_TIME) {
            return $this->ResponseGenericError('Время на ввод кода истекло.');
        }

        $accountInfo->fail_count++;
        $accountInfo->save();

        try {
            $cardService->ConfirmAccountDeletion(['clientcardid' => $this->appUser->client_card_id, 'code' => $data->code]);
        } catch (\Exception $e) {
            return $this->ResponseGenericError($e->getMessage());
        }

        $this->deleteUserData($this->appUser->id);

        Log::channel(self::LOG_CHANNEL)->info('confirm', [
            'ip' => $request->ip(),
            'appuser_id' => $this->appUser->id,
            'client_card_id' => $this->appUser->client_card_id,
            'phone' => $this->appUser->phone
        ]);

        return response()->json();
    }

    public function status(): \Illuminate\Http\JsonResponse
    {
        $accountInfo = AccountDeletionInfo::find($this->appUser->id);
        if (!$accountInfo || $accountInfo->date_sent->diffInRealSeconds(Carbon::now()) > self::$RETRY_TIME) {
            return response()->json([
                'progress' => false,
            ]);
        }

        return response()->json([
            'progress' => true,
        ]);
    }

    private function deleteUserData($userId) {
        try
        {
            DB::beginTransaction();

            AccountDeletionInfo::find($userId)?->delete();
            AppUser::find($userId)?->delete();
            CallCode::find($userId)?->delete();
            Feedback::where('user_id', $userId)->delete();
            Notification::where('user_id', $userId)->delete();
            Rating::where('user_id', $userId)->delete();
            Session::where('user_id', $userId)->delete();

            DB::table('article_view_log')->where('user_id', $userId)->delete();
            DB::table('notification_counter')->where('user_id', $userId)->delete();

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
        }
    }
}
