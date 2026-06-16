<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StoriesV2
 *
 * @property int $id
 * @property string $title
 * @property string $preview
 * @property int $duration
 * @property int $order
 * @property int $published
 * @property array $elements
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @mixin \Eloquent
 */

class StoriesV2 extends Model
{
    protected $table = 'stories_v2';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guarded = ['id'];

    protected $casts = [
        'elements' => 'array',
    ];
}
