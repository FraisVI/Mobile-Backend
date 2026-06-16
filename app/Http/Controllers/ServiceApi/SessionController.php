<?php

namespace App\Http\Controllers\ServiceApi;

use App\Models\AppUser;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionController extends ServiceApiController
{
    const LOG_CHANNEL = 'service-api';

    public function active(Request $request): \Illuminate\Http\JsonResponse
    {
        $userIds = Session::where(['registered' => 1, 'authorized' => 1])->groupBy('user_id')->pluck('user_id');
        $clientCardIds = AppUser::whereIn('id', $userIds)->pluck('client_card_id');

        $result = [];
        foreach ($clientCardIds as $cardId) {
            $obj = new \stdClass();
            $obj->clientcardid = $cardId;

            $result[] = $obj;
        }

        Log::channel(self::LOG_CHANNEL)->info('session/active', ['ip' => $request->ip(), 'count' => count($clientCardIds)]);

        return response()->json($result);
    }
}
