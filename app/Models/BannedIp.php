<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannedIp extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'ip';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['ip'];
    protected $table = 'banned_ips';
}
