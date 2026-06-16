<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpPhone extends Model
{
    protected $table = 'ip_phone';
    public $timestamps = false;

    protected $fillable = [
        'ip',
        'phone',
        'last_request',
        'attempts',
        'hour_ban',
        'second_hour_ban',
    ];

    protected $casts = [
        'last_request'    => 'datetime',
        'hour_ban'        => 'datetime',
        'second_hour_ban' => 'datetime',
    ];
}
