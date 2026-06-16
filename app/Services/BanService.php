<?php

namespace App\Services;

use App\Models\BannedIp;
use App\Models\BannedPhone;
use App\Models\Ip;
use App\Models\Phone;
use App\Models\IpPhone;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class BanService
{
    const ATTEMPT_WINDOW = 90;
    const PHONE_NUMBER = "SOME_PHONE";
    const LOG_CHANNEL = 'ban';

    protected array $allowedCountries = [
        'RU',
    ];

    /**
     * Проверить и обновить статус по IP + телефону
     *
     * @throws HttpResponseException
     */
    public function checkAndRecord(string $appVersion, string $ip, string $phone): void
    {

        if (empty($ip) || empty($phone)) {
            throw new HttpResponseException(response()->json([
                'message' => 'Некорректные входные данные: IP или номер телефона отсутствуют',
            ], 400));
        }

        $isBannedIp = BannedIp::find($ip);
        $isBannedPhone = BannedPhone::find($phone);

        if ($isBannedIp || $isBannedPhone) {
            $this->ban($appVersion, $ip, $phone);
        }

        $now = Carbon::now();
        $record = IpPhone::firstOrNew(['ip' => $ip, 'phone' => $phone]);

        if (!$record->exists) {
            Ip::firstOrCreate(['ip' => $ip]);
            Phone::firstOrCreate(['phone' => $phone]);

            $record->last_request = $now;
            $record->attempts = 1;
            $record->save();
            return;
        }

        if ($record->hour_ban && $now->lt($record->hour_ban)) {
            $this->ban($appVersion, $ip, $phone, 5);
        }

        if ($record->second_hour_ban && $now->lt($record->second_hour_ban)) {
            $this->ban($appVersion, $ip, $phone, 24 * 60);
        }

        if ($record->second_hour_ban && $now->gte($record->second_hour_ban)) {
            $this->ban($appVersion, $ip, $phone); // Вечный бан
        }

        $delta = $now->diffInSeconds($record->last_request);

        if ($delta < self::ATTEMPT_WINDOW) {
            if ($record->attempts === 0) {
                // Вторая попытка — бан на 5 минут
                $record->hour_ban = $now->copy()->addMinutes(5);
                $record->attempts = 0;
                $record->save();
                Log::channel(self::LOG_CHANNEL)->info('Ban triggered', [
                    'ip' => $ip,
                    'phone' => $phone,
                    'reason' => 'Повторная попытка в течении ' . self::ATTEMPT_WINDOW . ' секунд, бан на 5 минут',
                ]);
                $this->ban($appVersion, $ip, $phone, 5);
            } else {
                // Попытка после 5-минутного — бан на 24 часа
                $record->second_hour_ban = $now->copy()->addHours(24);
                $record->attempts = 0;
                $record->save();
                Log::channel(self::LOG_CHANNEL)->info('Пользователь заблокирован', [
                    'ip' => $ip,
                    'phone' => $phone,
                    'reason' => 'Повторная попытка в течении 5 минут, бан на 24 часа',
                ]);
                $this->ban($appVersion, $ip, $phone, 24 * 60);
            }
        }

        $record->last_request = $now;
        $record->attempts = 0;
        $record->save();
    }

    /**
     * Наложить бан
     *
     * @throws HttpResponseException
     */
    protected function ban(string $appVersion, string $ip, string $phone, ?int $minutes = null): void
    {
        if ($minutes === null) {
            BannedIp::firstOrCreate(['ip' => $ip]);
            BannedPhone::firstOrCreate(['phone' => $phone]);
            Log::channel(self::LOG_CHANNEL)->info('Пермаментный бан', [
                'ip' => $ip,
                'phone' => $phone,
                'reason' => 'Пермаментный бан',
            ]);
        }

        $this->returnMessage($appVersion);
    }

    /**
     * Формирование и выброс HttpResponseException с JSON‐ответом
     */
    protected function httpException(int $code, string $detail): HttpResponseException
    {
        return new HttpResponseException(response()->json([
            'message' => $detail
        ], $code));
    }

    /**
     * Снять бан
     */
    public function unban(?string $ip, ?string $phone): void
    {
        if ($ip) {
            BannedIp::where('ip', $ip)->delete();
            IpPhone::where('ip', $ip)->delete();
        }

        if ($phone) {
            BannedPhone::where('phone', $phone)->delete();
            $ips = IpPhone::where('phone', $phone)->pluck('ip')->unique()->all();
            BannedIp::whereIn('ip', $ips)->delete();
            IpPhone::where('phone', $phone)->delete();
            Ip::whereIn('ip', $ips)->delete();
        }
    }

    private function returnMessage(string $appVersion)
    {
        if ($appVersion && version_compare($appVersion, '0.0.21.4', '>=')) {
            $message = 'Ваш IP-адрес был временно заблокирован системой безопасности. Чтобы восстановить доступ, пожалуйста, свяжитесь с технической поддержкой по телефону: '
            . '<a href="tel:' . self::PHONE_NUMBER . '" style="color:red">' . self::PHONE_NUMBER . '</a>';
        } else {
            $message = 'Ваш IP-адрес был временно заблокирован системой безопасности. Чтобы восстановить доступ, пожалуйста, свяжитесь с технической поддержкой по телефону: ' . self::PHONE_NUMBER;
        }
        throw $this->httpException(400, $message);
    }
}
