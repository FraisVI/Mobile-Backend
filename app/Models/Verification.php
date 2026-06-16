<?php

namespace App\Models;

use App\Http\Controllers\Api\LoginController;
use App\SmsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * App\Models\Verification
 *
 * @property string $phone
 * @property string $code
 * @property \Illuminate\Support\Carbon $date_sent
 * @mixin \Eloquent
 * @method static Verification find(string $phone)
 */

class Verification extends Model
{
    protected $table = 'verification';
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = 'phone';
    protected $keyType = 'string';

    protected $casts = [
        'date_sent' => 'datetime:Y-m-d H:i:s',
    ];

    static function create($phone): Verification
    {
        $verification = new Verification();
        $verification->date_sent = Carbon::now();
        $verification->phone = $phone;
        $verification->generateCode();

        return $verification;
    }

    static function initialize($phone): Verification
    {
        $verification = Verification::find($phone);
        if ($verification) {
            if ($verification->date_sent->diffInRealSeconds() > LoginController::$SMS_RESEND_TIME) {
                $verification->date_sent = Carbon::now();
                $verification->generateCode();
                $verification->save();
                $verification->sendSms();
            }
        } else {
            $verification = Verification::create($phone);
            $verification->save();
            $verification->sendSms();
        }

        return $verification;
    }

    function generateCode() {
        if (in_array($this->phone, LoginController::TEST_PHONES)) {
            $this->code = LoginController::TEST_CODE;
        } else {
            $this->code = str_pad(rand(0, 999999), 6, 0, STR_PAD_LEFT);
        }
    }

    function sendSms() {
        if (in_array($this->phone, LoginController::TEST_PHONES)) {
            return; // don't send sms to test phones
        }

        $smsService = new SmsService();
        $smsService->Send($this->phone, "Ваш код: {$this->code}");
    }
}
