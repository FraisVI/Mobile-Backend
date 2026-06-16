<?php

namespace App\Http\Controllers\ServiceApi;

use App\Http\Controllers\Api\CallCodeController as CallCodeControllerApi;
use App\Models\AppUser;
use App\Models\CallCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallCodeController extends ServiceApiController
{
    const LOG_CHANNEL = 'service-api';

    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        CallCode::where('created_at', '<', Carbon::now()->subSeconds(CallCodeControllerApi::$CODE_EXPIRE_TIME))->delete();

        $q = CallCode::where('created_at', '>',
            Carbon::now()->subSeconds(CallCodeControllerApi::$CODE_EXPIRE_TIME)
        );

        $userIds = $q->pluck('appuser_id');
        $users = AppUser::whereIn('id', $userIds)->pluck('client_card_id', 'id')->toArray();

        $list = [];
        foreach ($q->get() as $c) {
            $obj = new \stdClass();
            $obj->clientcardid = $users[$c->appuser_id];
            $obj->code = $c->code;
            $list[] = $obj;
        }

        return response()->json($list);
    }

    public function validate(Request $request): \Illuminate\Http\JsonResponse
    {
        $code = $request->get('code');
        $q = CallCode::where('code', $code)->where('created_at', '>',
            Carbon::now()->subSeconds(CallCodeControllerApi::$CODE_EXPIRE_TIME)
        )->firstOrFail();

        $clientCardId = AppUser::findOrFail($q->appuser_id)->client_card_id;

        return response()->json(['clientcardid' => $clientCardId]);
    }
}
