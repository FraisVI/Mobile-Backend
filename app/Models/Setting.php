<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'key';
    protected $fillable = ['key', 'value'];

    public const NOTIFICATIONS_PRUNE_LIMIT = 'notifications_prune_limit';

    /**
     * Получить значение настройки. 0 = не удалять пуши.
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = 'setting.' . $key;
        return Cache::remember($cacheKey, 300, function () use ($key, $default) {
            $row = static::find($key);
            return $row !== null ? $row->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget('setting.' . $key);
    }
}
