<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use SxGeo;

class BlockByCountry
{
    protected array $allowedCountries = [
        'RU',
    ];

    /**
     * Обрабатывает входящий запрос.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        $countryCode = Cache::remember("geoip:{$ip}", 3600, function () use ($ip) {
            return SxGeo::get($ip);
        });
        if (!in_array($countryCode, $this->allowedCountries, true)) {
            throw $this->httpException(400, "Пожалуйста, отключите VPN, чтобы авторизоваться.");
        }

        return $next($request);
    }

    protected function httpException(int $code, string $detail): HttpResponseException
    {
        return new HttpResponseException(response()->json([
            'message' => $detail
        ], $code));
    }
}
