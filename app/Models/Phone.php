<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'phone';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['phone'];
}
