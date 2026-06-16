<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

final class FirebaseLogger
{
    /**
     * Управляемое логирование Firebase/FCM.
     *
     * - ENABLED=false отключает все логи FirebaseLogger.
     * - VERBOSE=true включает подробные INFO-логи (успехи/диспетчеризация и т.п.).
     */
    public const ENABLED = false;
    public const VERBOSE = false;

    public static function info(string $message, array $context = []): void
    {
        if (!self::ENABLED || !self::VERBOSE) {
            return;
        }
        Log::channel('firebase')->info($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        if (!self::ENABLED) {
            return;
        }
        Log::channel('firebase')->warning($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        if (!self::ENABLED) {
            return;
        }
        Log::channel('firebase')->error($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        if (!self::ENABLED) {
            return;
        }
        Log::channel('firebase')->critical($message, $context);
    }
}

