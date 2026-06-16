<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Session;
use Illuminate\Support\Facades\Log;

class CleanInvalidFcmTokens extends Command
{
    protected $signature = 'fcm:clean-invalid-tokens';
    protected $description = 'Удаляет сессии с недействительными или пустыми FCM токенами';

    public function handle(): int
    {
        $count = Session::whereNull('fcm_token')
            ->orWhere('fcm_token', '')
            ->delete();

        Log::info("Удалено {$count} сессий с недействительными FCM токенами");

        $this->info("Удалено {$count} токенов.");
        return 0;
    }
}

