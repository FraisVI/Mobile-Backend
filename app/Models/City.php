<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\City
 *
 * @property int $id
 * @property string $name
 * @mixin \Eloquent
 */

class City extends Model
{
    protected $table = 'cities';
    public $timestamps = false;
}
