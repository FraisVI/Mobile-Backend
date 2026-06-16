<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CallCode
 *
 * @property int $appuser_id
 * @property int $code
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @mixin \Eloquent
 */

class CallCode extends Model
{
    protected $table = 'callcode';
    protected $hidden = ['created_at', 'updated_at'];

    protected $primaryKey = 'appuser_id';
}
