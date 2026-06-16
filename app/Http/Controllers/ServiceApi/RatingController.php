<?php

namespace App\Http\Controllers\ServiceApi;

use App\Models\AppUser;
use App\Models\Rating;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class RatingController extends ServiceApiController
{
    const LOG_CHANNEL = 'service-api';

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, Messaging $messaging): JsonResponse
    {
        $validator = Validator::make($request->json()->all(), [
            'clientcardid' => 'required'
        ]);
        $validator->validate();

        $data = (object) $request->json()->all();
        $user = AppUser::where('client_card_id', $data->clientcardid)->firstOrFail();

        $rate_hash = Str::random(32);
        $rating = new Rating();
        $rating->hash = $rate_hash;
        $rating->user_id = $user->id;
        $rating->message_id = $data->messageid ?? null;
        $rating->order_id = $data->orderid ?? null;
        $rating->save();

        $nm = new \App\Models\Notification();
        $nm->user_id = $user->id;
        $title = 'Помогите нам стать лучше!';
        $nm->message = 'Оцените уровень сервиса';
        $nm->rate_hash = $rate_hash;
        $nm->label = 'Опрос';
        $nm->color = 'warning';
        $nm->save();

        if (!($user->notify & AppUser::NOTIFY_QOS)) {
            $sessions = Session::getUserSessions($user->id);
            if (count($sessions) > 0) {
                $messaging->sendMulticast(
                    CloudMessage::new()
                        ->withNotification(Notification::create($title, $nm->message))
                        ->withData(['rating' => $nm->rate_hash]),
                    $sessions
                );
            }
        }

        Log::channel(self::LOG_CHANNEL)->info('rating/create', [
            'ip' => $request->ip(),
            'client_card_id' => $data->clientcardid,
            'message_id' => $data->messageid,
            'order_id' => $data->orderid,
            'count' => count($sessions)
        ]);

        return response()->json();
    }

    public function completed(Request $request): JsonResponse
    {
        $data = (object) $request->json()->all();

        $ratings = Rating::where('filled', 1)->orderBy('created_at', 'DESC');

        if (isset($data->date_from)) {
            $ratings->where('created_at', '>=',
                Carbon::createFromFormat(self::DATE_TIME_FORMAT, $data->date_from)
            );
        }

        $ratings = $ratings->get();
        $ratings->makeHidden(['hash', 'user_id', 'filled', 'created_at', 'updated_at']);

        $userMap = AppUser::whereIn('id',
            $ratings->pluck('user_id')->unique()->values()->toArray()
        )->pluck('client_card_id', 'id')->toArray();

        foreach ($ratings as $r) {
            $r->created = $r->created_at->format(self::DATE_TIME_FORMAT);
            $r->updated = $r->updated_at->format(self::DATE_TIME_FORMAT);
            $r->clientcardid = $userMap[$r->user_id];
        }

        Log::channel(self::LOG_CHANNEL)->info('rating/completed', [
            'ip' => $request->ip(),
            'data' => $data,
            'count' => count($ratings)
        ]);

        return response()->json($ratings, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
