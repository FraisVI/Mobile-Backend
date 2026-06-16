<?php

namespace App\Http\Controllers\Admin;

use App\Models\AppUser;
use App\Models\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends AdminController
{
    const FIVE_MINUTES = 5 * 60;
    const SIGMA_BILLING_ID = 'SOME_ID';

    public function Show()
    {
        $title = 'Dashboard';

        $usersTotal = (int) AppUser::count();
        $usersWithTokens = (int) Session::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->count(DB::raw('DISTINCT user_id'));

        $users = (int) Session::whereNotNull('user_id')->count(DB::raw('DISTINCT user_id'));
        $android = (int) Session::where('uagent', 'LIKE', '%Android%')->whereNotNull('user_id')->count(DB::raw('DISTINCT user_id'));
        $ios = (int) Session::where('uagent', 'LIKE', '%iPhone%')->whereNotNull('user_id')->count(DB::raw('DISTINCT user_id'));

        $pct = $users > 0 ? round($android * 100 / $users) : 0;

        $sms_balance = $this->getSmsBalance();

        return view('admin.dashboard', compact('title', 'usersTotal', 'usersWithTokens', 'users', 'android', 'ios', 'pct', 'sms_balance'));
    }

    private function getSmsBalance(): string
    {
        $cacheKey = 'sms_balance';
        $cached = Cache::get($cacheKey);
        if ($cached !== null && $cached !== '—') {
            return (string) $cached;
        }

        $sigmaKey = env('ADMIN_SIGMA_KEY') ?: env('SIGMA_KEY');
        if (empty($sigmaKey)) {
            Log::channel('sms')->warning('SMS balance: ADMIN_SIGMA_KEY (or SIGMA_KEY) not set in .env');
            return '—';
        }

        try {
            $response = Http::timeout(5)->withHeaders([
                'Authorization' => $sigmaKey,
            ])->get('https://online.sigmasms.ru/api/billings/balance/' . self::SIGMA_BILLING_ID);

            $balance = $this->extractBalanceFromResponse($response);
            if ($balance !== null) {
                Cache::put($cacheKey, $balance, self::FIVE_MINUTES);
                return (string) $balance;
            }

            Log::channel('sms')->warning('SMS balance: unexpected API response', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::channel('sms')->warning('SMS balance: request failed', ['error' => $e->getMessage()]);
        }

        return '—';
    }

    private function extractBalanceFromResponse($response): ?string
    {
        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        if (is_numeric($data)) {
            return (string) $data;
        }
        if (is_array($data)) {
            foreach (['result', 'balance', 'data.balance', 'amount'] as $key) {
                $value = data_get($data, $key);
                if ($value !== null && $value !== '' && (is_numeric($value) || is_string($value))) {
                    return (string) $value;
                }
            }
        }

        return null;
    }
}
