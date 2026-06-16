<?php

namespace App\Http\Controllers\Api;

use App\CardService;
use App\Models\AppUser;
use App\Models\Segment;
use App\Services\FcmSubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends ApiController
{
    const LOG_CHANNEL = 'profile';

    /**
     * @throws \Exception
     */
    public function ProfileInformation(CardService $cardService, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $response = $cardService->getResponse('getclientcard', [
                'clientcardid' => $this->appUser->client_card_id
            ]);
            $this->appUser = AppUser::where('client_card_id', $this->appUser->client_card_id)->first();

            if ($response->successful()) {
                if ($this->appUser == null) {
                    $this->appUser = AppUser::create((object)$response->json());
                }

                $json = $response->json();

                if (array_key_exists('fio', $json)) {
                    $pieces = explode(' ', trim($json['fio']));
                    if (count($pieces) == 3) {
                        $this->appUser->lastname = $pieces[0];
                        $this->appUser->firstname = $pieces[1];
                        $this->appUser->middlename = $pieces[2];
                    } else if (count($pieces) == 2) {
                        $this->appUser->lastname = $pieces[0];
                        $this->appUser->firstname = $pieces[1];
                        $this->appUser->middlename = '';
                    } else if (count($pieces) == 1) {
                        $this->appUser->lastname = $pieces[0];
                        $this->appUser->firstname = '';
                        $this->appUser->middlename = '';
                    }
                }

                if (array_key_exists('birthdate', $json)) {
                    $this->appUser->birthdate = date('Y-m-d', strtotime($json['birthdate']));
                }

                if (array_key_exists('gender', $json)) {
                    if ($json['gender'] == "Женский") {
                        $this->appUser->gender = "f";
                    }

                    if ($json['gender'] == "Мужской") {
                        $this->appUser->gender = "m";
                    }
                }

                $this->appUser->bonus_count = $json['bonuscount'];
                $this->appUser->bonus_rate = $json['bonusrate'];
                $this->appUser->sum_next_level = $json['summnextlevel'];

                $this->appUser->save();

            }
        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error('Error fetching client card: ' . $e->getMessage(), [
                'client_card_id' => $this->appUser->client_card_id
            ]);
            $this->appUser = AppUser::where('client_card_id', $this->appUser->client_card_id)->first();
        }

        return response()->json([
            'card' => $this->appUser->client_card_id,
            'fio' => trim("{$this->appUser->lastname} {$this->appUser->firstname} {$this->appUser->middlename}"),
            'phone' => "+7" . $this->session->phone,
            'email' => $this->appUser->email,
            'bdate' => $this->appUser->birthdate->format('d.m.Y'),
            'bonusRate' => $this->appUser->bonus_rate,
            'bonus' => $this->appUser->bonus_count,
            'sumNextLevel' => $this->appUser->sum_next_level,
            'gender' => $this->appUser->gender,
            'check' => "{$this->appUser->check}",
            'notify' => "{$this->appUser->notify}",
            'counters' => $this->counters->getNotificationCounter(),
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SetFcmToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->json()->all(), [
            'fcm_token' => 'required'
        ]);
        $validator->validate();

        $data = (object)$request->json()->all();

        $oldToken = $this->session->fcm_token;
        $newToken = trim((string) $data->fcm_token);

        if ($this->session->fcm_token != $data->fcm_token) {
            if (!empty($oldToken) && $oldToken !== $newToken) {
                FcmSubscriptionService::unsubscribeTokenFromAllTopics($this->session->user_id, $oldToken);
            }
            $this->session->fcm_token = $newToken;
            $this->session->save();
        }

        if ($newToken !== '') {
            FcmSubscriptionService::subscribeTokenToAllTopics($this->session->user_id, $newToken);
        }

        return response()->json();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SetFio(CardService $cardService, Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->json()->all(), [
            'firstname' => 'required|min:2',
            'lastname' => 'required|min:2',
            'middlename' => 'nullable|min:2',
        ], $this->messages);
        $validator->validate();

        $data = (object)$request->json()->all();

        $fio = trim("{$data->lastname} {$data->firstname} {$data->middlename}");

        $cardService->PostClientCardById([
            'clientcardid' => $this->appUser->client_card_id,
            'fio' => $fio,
        ]);

        $this->appUser->firstname = $data->firstname;
        $this->appUser->lastname = $data->lastname;
        $this->appUser->middlename = $data->middlename;
        $this->appUser->save();

        return response()->json([
            'fio' => trim("{$this->appUser->lastname} {$this->appUser->firstname} {$this->appUser->middlename}")
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SetEmail(CardService $cardService, Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->json()->all(), [
            'email' => 'required|email',
        ], $this->messages);
        $validator->validate();

        $data = (object)$request->json()->all();

        $cardService->PostClientCardById([
            'clientcardid' => $this->appUser->client_card_id,
            'email' => $data->email,
        ]);

        $this->appUser->email = $data->email;
        $this->appUser->save();

        return response()->json([
            'email' => $this->appUser->email
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SetBdate(CardService $cardService, Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Запрещено менять дату рождения'
        ], 405);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SetGender(CardService $cardService, Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->json()->all(), [
            'gender' => ['required', Rule::in('f', 'm')],
        ], $this->messages);
        $validator->validate();

        $data = (object)$request->json()->all();

        if ($this->appUser->gender == $data->gender) {
            return response()->json([
                'gender' => $this->appUser->gender
            ]);
        }

        $cardService->PostClientCardById([
            'clientcardid' => $this->appUser->client_card_id,
            'gender' => $data->gender == 'm' ? 'Мужской' : 'Женский',
        ]);

        $this->appUser->gender = $data->gender;
        $this->appUser->save();

        return response()->json([
            'gender' => $this->appUser->gender
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SetCheck(CardService $cardService, Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->json()->all(), [
            'check' => ['required', Rule::in('0', '1', '2', '3')],
        ], $this->messages);
        $validator->validate();

        $data = (object)$request->json()->all();
        $check = intval($data->check);

        if ($this->appUser->check == $check) {
            return response()->json([
                'check' => $this->appUser->check
            ]);
        }

        $cardService->PostClientCardById([
            'clientcardid' => $this->appUser->client_card_id,
            'receivingchek' => AppUser::CheckEnumStr($check),
        ]);

        $this->appUser->check = $check;
        $this->appUser->save();

        return response()->json([
            'check' => $this->appUser->check
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function SetNotify(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->json()->all(), [
            'notify' => ['required', 'integer', 'between:0,7'],
        ], $this->messages);
        $validator->validate();

        $data = (object)$request->json()->all();
        $notify = intval($data->notify);

        if ($this->appUser->notify == $notify) {
            return response()->json([
                'notify' => $this->appUser->notify
            ]);
        }

        $this->appUser->notify = $notify;
        $this->appUser->save();

        return response()->json([
            'notify' => $this->appUser->notify
        ]);
    }
}
