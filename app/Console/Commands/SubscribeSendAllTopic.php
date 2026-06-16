<?php

namespace App\Console\Commands;

use App\Models\Segment;
use App\Models\Session;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;

/**
 * Подписывает всех пользователей с FCM-токеном на топик общей рассылки SendAll.
 * Вызов: php artisan firebase:subscribe-send-all
 */
class SubscribeSendAllTopic extends Command
{
    protected $signature = 'firebase:subscribe-send-all
                            {--dry-run : Только показать количество токенов, не вызывать API}
                            {--chunk=1000 : Размер пачки токенов (макс. 1000 по лимиту FCM)}';

    protected $description = 'Подписать всех существующих пользователей с FCM-токеном на топик SOME-TOPIC (общая рассылка)';

    public function handle(): int
    {
        $topic = Segment::TOPIC_SEND_ALL;
        $chunkSize = min(1000, max(1, (int) $this->option('chunk')));
        $dryRun = $this->option('dry-run');

        $tokens = Session::query()
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        $total = count($tokens);
        $this->info("Топик: {$topic}. Найдено уникальных FCM-токенов: {$total}.");

        if ($total === 0) {
            $this->warn('Нет токенов для подписки.');
            return 0;
        }

        if ($dryRun) {
            $this->info('Режим --dry-run: API не вызывается.');
            $this->info("Будет отправлено запросов к FCM: " . (int) ceil($total / $chunkSize));
            return 0;
        }

        /** @var Messaging|null $messaging */
        $messaging = null;
        try {
            $messaging = app('firebase.messaging');
        } catch (\Throwable $e) {
            $this->error('Не удалось получить Firebase Messaging: ' . $e->getMessage());
            return 1;
        }

        $chunks = array_chunk($tokens, $chunkSize);
        $successChunks = 0;
        $failedChunks = 0;

        $bar = $this->output->createProgressBar(count($chunks));
        $bar->start();

        foreach ($chunks as $index => $chunk) {
            try {
                $messaging->subscribeToTopics([$topic], $chunk);
                $successChunks++;
            } catch (FirebaseException $e) {
                $failedChunks++;
                $this->newLine();
                $this->warn("Пачка #{$index}: " . $e->getMessage());
            } catch (\Throwable $e) {
                $failedChunks++;
                $this->newLine();
                $this->warn("Пачка #{$index}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Готово. Успешно пачек: {$successChunks}, с ошибками: {$failedChunks}.");

        return $failedChunks > 0 ? 1 : 0;
    }
}
