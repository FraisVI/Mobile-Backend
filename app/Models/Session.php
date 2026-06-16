<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\Session
 *
 * @property string $id
 * @property \Illuminate\Support\Carbon $lastused
 * @property string $phone
 * @property string $fcm_token
 * @property int $authorized
 * @property int $registered
 * @property string $data
 * @property int $user_id
 * @property string $uagent
 * @property string $version
 * @mixin \Eloquent
 */

class Session extends Model
{
    protected $table = 'session';
    public $timestamps = false;

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
        'lastused' => 'datetime:Y-m-d H:i:s',
    ];

    static function create($phone): Session
    {
        $session = new Session();
        $session->id = Str::random(40);
        $session->lastused = Carbon::now();
        $session->phone = $phone;
        $session->uagent = Str::limit($_SERVER['HTTP_USER_AGENT'], 255, '');

        return $session;
    }

    static function getUserSessions($user_id) : array
    {
        return Session::whereIn('user_id', is_array($user_id) ? $user_id : [$user_id])->whereNotNull('fcm_token')->get()->unique('fcm_token')->pluck('fcm_token')->all();
    }
}
