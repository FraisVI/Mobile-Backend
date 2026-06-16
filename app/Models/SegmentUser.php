<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SegmentUser extends Model
{
    protected $table = 'segment_user';

    public $incrementing = false;

    protected $fillable = ['segment_id', 'user_id'];

    protected $casts = [
        'segment_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }
}
