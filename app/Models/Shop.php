<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Shop
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $text
 * @property string $phone
 * @property string $working_hours
 * @property float $lat
 * @property float $lon
 * @property array $images
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @mixin \Eloquent
 */

class Shop extends Model
{
    protected $table = 'shop';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guarded = ['id', 'images'];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'images' => 'array',
        //'created_at' => 'datetime:d.m.Y',
        //'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}
