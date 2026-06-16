<?php

namespace App\Repositories;

use App\Models\Session;

final class FcmTokenRepository
{
    public function invalidate(string $token): void
    {
        Session::where('fcm_token', $token)->update(['fcm_token' => null]);
    }

    public function invalidateMany(array $tokens): void
    {
        Session::whereIn('fcm_token', $tokens)->update(['fcm_token' => null]);
    }
}

