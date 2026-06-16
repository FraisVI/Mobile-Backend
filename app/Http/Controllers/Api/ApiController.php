<?php

namespace App\Http\Controllers\Api;

use App\Models\AppUser;
use App\Models\Session;
use App\NotificationCounterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    protected array $messages = [
        'required' => 'Поле должно быть заполнено.',
        'phone.regex' => 'Вы можете ввести только номер мобильного телефона.',
        'code.regex' => 'Введите код из SMS сообщения.',
        'email.email' => 'Укажите корректный email адрес.',
        'bdate.date_format' => 'Введите дату цифрами в формате 00.00.0000',
        'bdate.before_or_equal' => 'Вы должны быть старше 18 лет.',
        'bdate.after_or_equal' => 'Введите дату цифрами в формате 00.00.0000',
        'terms.accepted' => 'Вы должны принять условия, чтобы продолжить.',
        'min' => 'Длина поля должна быть :min или более символов.',
        'max' => 'Превышена длина в :max символов.'
    ];

    protected ?Session $session = null;
    protected ?AppUser $appUser = null;
    protected ?NotificationCounterService $counters = null;

    function __construct(Request $request)
    {
        if ($request->hasHeader('Authorization')) {
            $sid = $request->header('Authorization');
            $this->session = Session::find($sid);
            if ($this->session) {
                if ($request->hasHeader('X-App-Version')) {
                    $this->session->version = $request->header('X-App-Version');
                }
                $this->session->lastused = Carbon::now();
                $this->session->save();

                if ($this->session->user_id > 0) {
                    $this->appUser = AppUser::find($this->session->user_id);
                    $this->counters = new NotificationCounterService($this->appUser);
                }
            }
        }

        $this->middleware(function ($request, $next) {
            if ($request->route()->controller instanceof LoginController) {
                return $next($request);
            }

            if (!$this->session || $this->session->registered != 1 || $this->session->authorized != 1) {
                return $this->ResponseUnauthorized();
            }

            return $next($request);
        });
    }

    protected function ResponseUnauthorized(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Вы не авторизованы'
        ], 401);
    }

    protected function ResponseGenericError($message): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message
        ], 400);
    }
}
