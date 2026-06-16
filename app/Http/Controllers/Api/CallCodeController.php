<?php

namespace App\Http\Controllers\Api;

use App\Models\CallCode;
use Carbon\Carbon;

class CallCodeController extends ApiController
{
    public static int $CODE_EXPIRE_TIME = 60 * 5; // 5min
    public static int $CODE_REGENERATE_TIME = 60 * 3; // 3min

    public function Create(): \Illuminate\Http\JsonResponse
    {
        /** @var CallCode $callcode */
        $callcode = CallCode::where('appuser_id', $this->appUser->id)->first();
        if ($callcode == null || $callcode->created_at->lessThan(Carbon::now()->subSeconds(self::$CODE_REGENERATE_TIME)))
        {
            if ($callcode == null) {
                $callcode = new CallCode();
                $callcode->appuser_id = $this->appUser->id;
            }

            $callcode->code = str_pad(rand(0, 999999), 6, 0, STR_PAD_LEFT);
            $callcode->created_at = Carbon::now();
            $callcode->updated_at = Carbon::now();
            $callcode->save();
        }

        return response()->json([
            'code' => $callcode->code,
            'remaining' => self::$CODE_EXPIRE_TIME - $callcode->created_at->diffInRealSeconds(),
        ]);
    }
}
