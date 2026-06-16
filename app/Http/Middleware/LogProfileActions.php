<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogProfileActions
{
    public function handle(Request $request, Closure $next)
    {
        $channel = Log::channel('profile');

        $channel->info('Profile API started', [
            'method' => $request->method(),
            'uri' => $request->path(),
            'ip' => $request->ip(),
        ]);

        $start = microtime(true);

        try {
            $response = $next($request);

            $duration = round((microtime(true) - $start) * 1000, 2);
            $responseData = $this->extractResponseData($response);
            $sanitizedResponse = $this->sanitize($responseData);

            $channel->info('Profile API finished', [
                'status' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'response' => $sanitizedResponse,
            ]);

            return $response;
        } catch (Throwable $e) {
            $channel->error('Profile API failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Удаляет потенциально чувствительные данные из массива/строки.
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (preg_match('/(password|token|secret)/i', $key)) {
                    $value = '[REDACTED]';
                } elseif (is_array($value)) {
                    $value = $this->sanitize($value);
                } elseif (is_string($value) && mb_strlen($value) > 500) {
                    $value = mb_substr($value, 0, 500) . '...';
                }
            }
        } elseif (is_string($data) && mb_strlen($data) > 500) {
            $data = mb_substr($data, 0, 500) . '...';
        }

        return $data;
    }

    /**
     * Извлекает тело ответа для логирования (если JSON, возвращает декодированный массив).
     */
    protected function extractResponseData($response)
    {
        $contentType = $response->headers->get('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $json = json_decode($response->getContent(), true);
            return $json ?? ['raw' => '[invalid json]'];
        }

        return [
            'type' => $contentType,
            'length' => strlen($response->getContent()),
        ];
    }
}
