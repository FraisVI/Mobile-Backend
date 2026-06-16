<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notification
 *
 * @property int $id
 * @property int $user_id
 * @property int $article_id
 * @property string $rate_hash
 * @property string $message
 * @property string $label
 * @property string $color
 * @property \Illuminate\Support\Carbon $created_at
 * @mixin \Eloquent
 */

class Notification extends Model
{
    protected $table = 'notification';
    protected $hidden = ['user_id'];

    protected $fillable = [
        'user_id', 'article_id', 'rate_hash', 'message', 'label', 'color'
    ];

    protected $casts = [
        'created_at' => 'datetime:d.m.Y',
        //'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}

