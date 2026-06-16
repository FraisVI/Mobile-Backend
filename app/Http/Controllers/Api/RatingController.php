<?php

namespace App\Http\Controllers\Api;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends ApiController
{
    public function get($hash): \Illuminate\Http\JsonResponse
    {
        /** @var Rating $rating */
        $rating = Rating::where('user_id', $this->session->user_id)->where('hash', $hash)->firstOrFail();

        return response()->json($rating, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function submit(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = (object) $request->all();

        /** @var Rating $rating */
        $rating = Rating::where('user_id', $this->session->user_id)->where('hash', $data->hash)->firstOrFail();

        $rating->rating = intval($data->rating);
        $rating->message = $data->message;
        $rating->filled = 1;
        $rating->save();

        return response()->json($rating, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
