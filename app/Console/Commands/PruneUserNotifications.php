<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneUserNotifications extends Command
{
    protected $signature = 'notifications:prune {--limit= : Переопределить лимит из настроек}';
    protected $description = 'Удаляет старые пуши у пользователей, оставляя не более N последних. Лимит задаётся в настройках (0 = не удалять).';

    public function handle(): int
    {
        $optionLimit = $this->option('limit');
        $limit = $optionLimit !== null && $optionLimit !== ''
            ? (int) $optionLimit
            : (int) Setting::get(Setting::NOTIFICATIONS_PRUNE_LIMIT, 10);

        if ($limit < 0) {
            $this->error('Лимит не может быть отрицательным.');
            return 1;
        }

        if ($limit === 0) {
            $this->info('Удаление отключено (в настройках указано 0).');
            return 0;
        }

        $userIds = Notification::select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > ?', [$limit])
            ->pluck('user_id');

        $totalDeleted = 0;

        foreach ($userIds as $userId) {
            $cutoff = Notification::where('user_id', $userId)
                ->orderByDesc('created_at')
                ->skip($limit - 1)
                ->take(1)
                ->value('created_at');

            if ($cutoff !== null) {
                $deleted = Notification::where('user_id', $userId)
                    ->where('created_at', '<', $cutoff)
                    ->delete();
                $totalDeleted += $deleted;
            }
        }

        if ($totalDeleted > 0) {
            Log::info("notifications:prune — удалено пушей: {$totalDeleted}, пользователей обработано: " . $userIds->count());
        }

        $this->info("Удалено пушей: {$totalDeleted}. Пользователей с обрезкой: " . $userIds->count());
        return 0;
    }
}
