<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Rating
 *
 * @property string $hash
 * @property int $user_id
 * @property string $message_id
 * @property string $order_id
 * @property int $rating
 * @property string $message
 * @property int $filled
 * @property AppUser $user
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|Rating where($column, $operator = null, $value = null, $boolean = 'and')
 * @mixin Builder
 */

class Rating extends Model
{
    protected $table = 'rating';
    protected $guarded = ['hash'];

    public $incrementing = false;
    protected $primaryKey = 'hash';
    protected $keyType = 'string';

    protected $casts = [
        'hash' => 'string',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AppUser::class, 'id', 'user_id');
    }
}
