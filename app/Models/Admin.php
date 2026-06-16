<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class Admin extends Model
{
    protected $table = 'admins';

    protected $fillable = [
        'name',
        'password',
        'role',
    ];

    public static function findAdmin($name, $pass)
    {
        $admin = self::where('name', $name)->first();

        if ($admin && hash('sha256', $pass) === $admin->password) {
            return $admin;
        }

        return null;
    }

}
