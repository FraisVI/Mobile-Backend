<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidateSmartCaptcha
{
    private const MIN_APP_VERSION = '0.0.20';
    private const LOG_CHANNEL = 'yandex-captcha';
    private const ENABLE_LOGGING = false; // Включить/выключить логирование

    public function handle(Request $request, Closure $next)
    {
        $appVersion = $request->header('X-App-Version');
        $ip = $request->ip();
        $path = $request->path();
        $method = $request->method();

        $this->log('info', 'ValidateSmartCaptcha: Request received', [
            'ip' => $ip,
            'path' => $path,
            'method' => $method,
            'app_version' => $appVersion,
            'min_version' => self::MIN_APP_VERSION,
        ]);

        $shouldValidate = $this->shouldValidateCaptcha($appVersion);

        $this->log('info', 'ValidateSmartCaptcha: Validation decision', [
            'ip' => $ip,
            'path' => $path,
            'should_validate' => $shouldValidate,
            'app_version' => $appVersion,
        ]);

        if ($shouldValidate) {
            if ($errorResponse = $this->checkCaptchaToken($request)) {
                return $errorResponse;
            }
        } else {
            $this->log('info', 'ValidateSmartCaptcha: Validation skipped', [
                'ip' => $ip,
                'path' => $path,
                'reason' => 'App version is below minimum required version',
                'app_version' => $appVersion,
            ]);
        }

        $this->log('info', 'ValidateSmartCaptcha: Request passed', [
            'ip' => $ip,
            'path' => $path,
        ]);

        return $next($request);
    }

    /**
     * Определяет, нужно ли валидировать капчу.
     * Версия >= MIN_APP_VERSION → true.
     */
    private function shouldValidateCaptcha(?string $appVersion): bool
    {
        return !$appVersion || version_compare($appVersion, self::MIN_APP_VERSION, '>=');
    }

    /**
     * Проверяет CAPTCHA через Yandex SmartCaptcha.
     */
    private function checkCaptchaToken(Request $request): ?\Illuminate\Http\JsonResponse
    {
        $token = $request->input('captcha_token');
        $ip = $request->ip();
        $path = $request->path();

        $this->log('info', 'ValidateSmartCaptcha: Starting token validation', [
            'ip' => $ip,
            'path' => $path,
            'token_present' => !empty($token),
            'token_length' => $token ? strlen($token) : 0,
        ]);

        if (!$token) {
            $this->log('warning', 'ValidateSmartCaptcha: Token missing', [
                'ip' => $ip,
                'path' => $path,
                'error' => 'captcha_token is required',
            ]);

            return response()->json([
                'message' => 'Отсутствует captcha'
            ], 422);
        }

        $secret = config('services.yandex_captcha.secret');
        $hasSecret = !empty($secret);

        $this->log('info', 'ValidateSmartCaptcha: Preparing Yandex API request', [
            'ip' => $ip,
            'path' => $path,
            'has_secret' => $hasSecret,
            'token_preview' => substr($token, 0, 10) . '...',
        ]);

        if (!$hasSecret) {
            $this->log('error', 'ValidateSmartCaptcha: Secret key missing', [
                'ip' => $ip,
                'path' => $path,
                'error' => 'Yandex Captcha secret key is not configured',
            ]);
        }

        $startTime = microtime(true);

        try {
            $response = Http::asForm()->post('https://smartcaptcha.yandexcloud.net/validate', [
                'secret' => $secret,
                'token'  => $token,
                'ip'     => $ip,
            ]);

            $elapsedTime = round((microtime(true) - $startTime) * 1000, 2);
            $statusCode = $response->status();
            $responseBody = $response->json();

            $this->log('info', 'ValidateSmartCaptcha: Yandex API response received', [
                'ip' => $ip,
                'path' => $path,
                'status_code' => $statusCode,
                'elapsed_ms' => $elapsedTime,
                'response_status' => $responseBody['status'] ?? 'unknown',
                'response_message' => $responseBody['message'] ?? null,
            ]);

            if ($response->failed()) {
                $this->log('error', 'ValidateSmartCaptcha: Yandex API request failed', [
                    'ip' => $ip,
                    'path' => $path,
                    'status_code' => $statusCode,
                    'elapsed_ms' => $elapsedTime,
                    'response_body' => $responseBody,
                    'error' => 'HTTP request to Yandex SmartCaptcha failed',
                ]);

                return response()->json([
                    'message' => 'Ошибка проверки captcha',
                ], 500);
            }

            $data = $response->json();

            if (!isset($data['status']) || $data['status'] !== 'ok') {
                $this->log('warning', 'ValidateSmartCaptcha: Token validation failed', [
                    'ip' => $ip,
                    'path' => $path,
                    'status' => $data['status'] ?? 'missing',
                    'response_data' => $data,
                    'reason' => 'Token status is not "ok"',
                ]);

                return response()->json([
                    'message' => 'Неверная captcha'
                ], 422);
            }

            $this->log('info', 'ValidateSmartCaptcha: Token validation successful', [
                'ip' => $ip,
                'path' => $path,
                'status' => $data['status'],
                'elapsed_ms' => $elapsedTime,
            ]);

            return null;

        } catch (\Exception $e) {
            $elapsedTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->log('error', 'ValidateSmartCaptcha: Exception during validation', [
                'ip' => $ip,
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'elapsed_ms' => $elapsedTime,
            ]);

            return response()->json([
                'message' => 'Ошибка проверки captcha',
            ], 500);
        }
    }

    /**
     * Логирует сообщение, если логирование включено.
     *
     * @param string $level Уровень логирования (info, warning, error и т.д.)
     * @param string $message Сообщение для логирования
     * @param array $context Контекстные данные
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if (self::ENABLE_LOGGING) {
            Log::channel(self::LOG_CHANNEL)->{$level}($message, $context);
        }
    }
}
