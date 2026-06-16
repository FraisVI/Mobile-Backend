<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'ip';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['ip'];
}
