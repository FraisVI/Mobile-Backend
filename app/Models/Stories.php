<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Stories
 *
 * @property int $id
 * @property string $title
 * @property string $thumb
 * @property string $url
 * @property string $href
 * @property string $type
 * @property int $duration
 * @property int $order
 * @property int $published
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @mixin \Eloquent
 */

class Stories extends Model
{
    protected $table = 'stories';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guarded = ['id'];
}
