<?php

namespace App\Http\Controllers\Api;

use App\CardService;
use App\Events\UserLogged;
use App\Exceptions\EntityNotFound;
use App\Models\AppUser;
use App\Models\Segment;
use App\Models\Session;
use App\Models\Verification;
use App\Services\BanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class LoginController extends ApiController
{
    const TEST_PHONES = ["PHONE", "PHONE_AppStore"];
    const TEST_CODE = "000000";

    const LOG_CHANNEL = 'account-registration';

    public static int $SMS_RESEND_TIME = 90;

    protected BanService $banService;

    public function __construct(BanService $banService, Request $request)
    {
        parent::__construct($request);
        $this->banService = $banService;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function AppLogin(CardService $cardService, Request $request)
    {
        $data = (object) $request->json()->all();
        $validator = Validator::make($request->json()->all(), [
            'phone' => 'required|regex:/^79[0-9]{9}$/'
        ], $this->messages);
        $validator->validate();
        $data->phone = substr($data->phone, 1);

        if ($this->session && $this->session->authorized == 1) {
            if ($this->session->registered == 0 && $data->phone == $this->session->phone) {
                return $this->ResponseGenericError("REGISTER");
            } else {
                $this->session->authorized = 0;
                $this->session->registered = 0;
                $this->session->data = null;
                $this->session->save();
            }
        }

        $appUser = null;
        try {
            $appUser = $cardService->GetClientCardByPhone($data->phone);
        }
        catch (EntityNotFound $e) {
            // Пользователь не найден - регистрация дальше
        }
        catch (\Exception $e) {
            return $this->ResponseGenericError($e->getMessage());
        }

        // Если банов нет — отправляем код подтверждения (проверка в Middleware)
        $verification = Verification::initialize($data->phone);

        if ($this->session == null) {
            $this->session = Session::create($data->phone);
        }

        if ($this->session->phone != $data->phone) {
            $this->session->phone = $data->phone;
        }

        if ($appUser == null) {
            $this->session->data = null;
            $this->session->registered = 0;
        } else {
            $this->session->data = $appUser->toJson();
            $this->session->registered = 1;
        }
        $this->session->save();

        return response()->json([
            'sid'       => $this->session->id,
            'remaining' => self::$SMS_RESEND_TIME - $verification->date_sent->diffInRealSeconds()
        ]);
    }

    public function AppLoginResend(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$this->session) {
            return $this->ResponseUnauthorized();
        }

        if ($this->session->authorized) {
            return $this->ResponseGenericError("Вы уже авторизованы");
        }

        $verification = Verification::initialize($this->session->phone);

        return response()->json([
            "remaining" => self::$SMS_RESEND_TIME - $verification->date_sent->diffInRealSeconds()
        ]);
    }

    public function AppLoginVerify(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = (object)$request->json()->all();
        $validator = Validator::make($request->json()->all(), [
            'code' => 'required|regex:/^[0-9]{6}$/'
        ]);
        $validator->validate();

        if (!$this->session) {
            return $this->ResponseUnauthorized();
        }

        if ($this->session->authorized) {
            return $this->ResponseGenericError("Вы уже авторизованы");
        }

        $verification = Verification::find($this->session->phone);
        if (!$verification) {
            return $this->ResponseGenericError("Вы уже авторизованы");
        }

        if ($verification->date_sent->diffInRealSeconds() > 15 * 60) {
            return $this->ResponseGenericError("Время на ввод кода истекло");
        }

        if ($verification->code != $data->code) {
            return $this->ResponseGenericError("Проверочный код неверный");
        }

        /** @var AppUser $appUser */
        $appUser = AppUser::wherePhone($this->session->phone)->first();
        if (!$appUser) {
            $appUser = new AppUser();
        }

        $appUser->phone = $this->session->phone;
        if ($this->session->data) {
            $appUser->fill(json_decode($this->session->data, true));
            $appUser->save();

            $this->session->data = null;
            $this->session->user_id = $appUser->id;
        }

        $this->session->authorized = 1;
        $this->session->save();

        $verification->delete();

        if ($this->session->registered == 1) {
            UserLogged::dispatch($appUser->client_card_id);
        }

        return response()->json([
            'forwardToRegistration' => $this->session->registered == 0,
        ]);
    }

    public function AppRegister(Request $request, CardService $cardService): \Illuminate\Http\JsonResponse
    {
        $data = (object)$request->json()->all();
        $validator = Validator::make($request->json()->all(), [
            'firstname'     => ['required', 'max:64'],
            'middlename'    => ['required', 'max:64'],
            'lastname'      => ['required', 'max:64'],
            'email'         => ['required', 'email:rfc,dns', 'max:64'],
            'bdate'         => ['required', 'date_format:d.m.Y', 'before_or_equal:' . Carbon::now()->subYears(18)->format('d.m.Y'), 'after_or_equal:01.01.1900'],
            'gender'        => ['required', Rule::in('f', 'm')],
            'terms'         => ['required', 'accepted'],
            'subscriptions' => ['required'],
        ], $this->messages);
        $validator->validate();

        $data->firstname = Str::of($data->firstname)->trim()->lower()->ucfirst();
        $data->middlename = Str::of($data->middlename)->trim()->lower()->ucfirst();
        $data->lastname = Str::of($data->lastname)->trim()->lower()->ucfirst();

        if ($this->session->registered == 1) {
            return $this->ResponseGenericError("Вы уже зарегистрированы.");
        }

        $userData = [
            "phone"        => $this->session->phone,
            "fio"          => "{$data->lastname} {$data->firstname} {$data->middlename}",
            "email"        => $data->email,
            "birthdate"    => Carbon::parse($data->bdate)->format('Y-m-d') . 'T00:00:00Z',
            "gender"       => $data->gender == 'm' ? 'Мужской' : 'Женский',
            "unsubscribe"  => !($data->subscriptions == true),
            "receivingchek"=> 'Не отправлять',
        ];

        $response = $cardService->NewClientCard($userData);
        if ($response->successful())
        {
            $appUser = AppUser::create((object)$response->json());
            $appUser->save();

            $this->session->user_id = $appUser->id;
            $this->session->registered = 1;
            $this->session->save();
        }
        else if ($response->status() == 409)
        {
            $appUser = $cardService->GetClientCardByPhone($this->session->phone);
            if ($appUser->phone == $this->session->phone) {
                $existedAppUser = AppUser::where('phone', $this->session->phone)->first();
                if ($existedAppUser) {
                    $this->session->user_id = $existedAppUser->id;
                } else {
                    $appUser->save();
                    $this->session->user_id = $appUser->id;
                }
                $this->session->registered = 1;
                $this->session->save();
            } else {
                Log::channel(self::LOG_CHANNEL)->info('conflict', [
                    'ip'         => $request->ip(),
                    'phone_int'  => $this->session->phone,
                    'phone_ext'  => $appUser->phone,
                    'card_ext'   => $appUser->client_card_id,
                ]);
                return $this->ResponseGenericError('Конфликт пользовательских данных. Мы свяжемся с вами, когда устраним проблему.');
            }

            Log::channel(self::LOG_CHANNEL)->info('already_registered', [
                'ip'    => $request->ip(),
                'phone' => $this->session->phone,
                'card'  => $appUser->client_card_id,
            ]);
        }
        else
        {
            Log::channel(self::LOG_CHANNEL)->info('register', [
                'ip'       => $request->ip(),
                'status'   => $response->status(),
                'response' => $response->body(),
                'data'     => $userData,
            ]);

            return $this->ResponseGenericError($response->json()['Message']);
        }

        UserLogged::dispatch($appUser->client_card_id);

        if (!empty($this->session->fcm_token)) {
            \App\Services\FcmSubscriptionService::subscribeTokenToAllTopics(
                $this->session->user_id,
                $this->session->fcm_token
            );
        }

        return response()->json();
    }

    public function AppLogout(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$this->session) {
            return $this->ResponseUnauthorized();
        }

        $this->session->delete();
        return response()->json();
    }
}
