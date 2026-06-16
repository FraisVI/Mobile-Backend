<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\AppUser
 *
 * @property int $id
 * @property string $client_card_id
 * @property string $firstname
 * @property string $lastname
 * @property string $middlename
 * @property \Illuminate\Support\Carbon $birthdate
 * @property string $gender
 * @property string $email
 * @property string $phone
 * @property int $bonus_count
 * @property int $bonus_rate
 * @property int $sum_next_level
 * @property int $subscription
 * @property int $check
 * @property int $notify
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|AppUser wherePhone($value)
 * @method static AppUser|null find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|AppUser whereIn(string $key, int[] $array)
 * @method static \Illuminate\Database\Eloquent\Builder|AppUser where(string $key, string $value)
 */

class AppUser extends Model
{
    protected $table = 'appuser';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guarded = [];

    protected $casts = [
        'notify' => 'integer',
        'birthdate' => 'datetime:Y-m-d',
    ];

    const NOTIFY_SERVICE = 1;
    const NOTIFY_QOS     = 2;
    const NOTIFY_SPECIAL = 4;

    public function fio(): string
    {
        return trim("{$this->lastname} {$this->firstname} {$this->middlename}");
    }

    public function shortFio(): string
    {
        return trim("{$this->lastname} {$this->firstname}");
    }

    static function create($data): AppUser
    {
        $phone = Str::substr($data->phone, 1);
        $user = new AppUser();

        $user->client_card_id = $data->clientcardid;
        try {
            $pieces = explode(' ', $data->fio);
            if (count($pieces) == 3) {
                $user->firstname = $pieces[1];
                $user->lastname = $pieces[0];
                $user->middlename = $pieces[2];
            } if (count($pieces) == 2) {
                $user->firstname = $pieces[1];
                $user->lastname = $pieces[0];
                $user->middlename = '';
            } else {
                $user->firstname = $data->fio;
                $user->lastname = '';
                $user->middlename = '';
            }
        } catch (\Exception $e) {
            $user->firstname = '';
            $user->lastname = '';
            $user->middlename = '';
        }
        $user->birthdate = Carbon::parse($data->birthdate);
        $user->gender = ($data->gender == "Мужской") ? 'm' : (($data->gender == "Женский") ? 'f' : '');
        $user->email = $data->email;
        $user->phone = $phone;
        $user->bonus_count = intval($data->bonuscount);
        $user->bonus_rate = intval($data->bonusrate);
        $user->sum_next_level = intval($data->summnextlevel);
        $user->check = self::CheckEnumInt($data->receivingchek);

        return $user;
    }

    /*
     *  0 - Не указано
        1 - Электронное письмо
        2 - Сообщение SMS (Viber)
        3 - Не отправлять
     */
    static function CheckEnumInt(string $value): int
    {
        return match ($value) {
            "Электронное письмо" => 1,
            "Сообщение SMS (Viber)" => 2,
            "Не отправлять" => 3,
            default => 0,
        };
    }

    static function CheckEnumStr(int $value): string
    {
        return match ($value) {
            1 => "Электронное письмо",
            2 => "Сообщение SMS (Viber)",
            3 => "Не отправлять",
            0 => "",
        };
    }
}
