<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Article
 *
 * @property int $id
 * @property int $type_id
 * @property string $image
 * @property string $image_small
 * @property string $href
 * @property string $title
 * @property string $subtitle
 * @property string $content
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $published
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @method static \Illuminate\Database\Query\Builder whereIn(string $string, int[] $array)
 */

class Article extends Model
{
    protected $table = 'article';
    protected $hidden = ['start_at', 'end_at', 'created_at', 'updated_at', 'published'];
    protected $guarded = ['id'];

    protected $casts = [
        'start_at' => 'datetime:Y-m-d H:i:s',
        'end_at' => 'datetime:Y-m-d H:i:s',
    ];

}
