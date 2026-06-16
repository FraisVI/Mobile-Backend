<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    // Явно указываем имя таблицы, если не используется стандартное jobs
    protected $table = 'jobs';

    // Laravel не будет автоматически работать с created_at/updated_at как датами
    public $timestamps = false;

    // Указываем список заполняемых полей (если нужно)
    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    // Если хочешь, можешь добавить кастинг timestamp datetime
    protected $casts = [
        'reserved_at' => 'datetime',
        'available_at' => 'datetime',
        'created_at'   => 'datetime',
    ];
}
