<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Feedback
 *
 * @property int $id
 * @property int $user_id
 * @property int $from_user
 * @property array $options
 * @property string $message
 * @property int $viewed
 * @property \Illuminate\Support\Carbon $created_at
 * @mixin \Eloquent
 */

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $hidden = ['user_id', 'options'];
    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
    ];

}
