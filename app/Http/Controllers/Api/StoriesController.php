<?php

namespace App\Http\Controllers\Api;

use App\Facades\URLHelper;
use App\Models\Stories;
use App\Models\StoriesV2;
use Illuminate\Http\Request;
class StoriesController extends ApiController
{
    public function List(Request $request): \Illuminate\Http\JsonResponse
    {
        $stories = Stories::where('published', 1)->orderBy('order')->get();
        foreach ($stories as $story) {
            $story->url = URLHelper::transform($story->url);
            $story->thumb = URLHelper::transform($story->thumb);
        }

        return response()->json($stories, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function ListV2(Request $request): \Illuminate\Http\JsonResponse
    {
        $stories = StoriesV2::where('published', 1)->orderBy('order')->get();
        foreach ($stories as $story) {
            $story->preview = URLHelper::transform($story->preview);

            $elements = $story->elements;
            foreach ($elements as &$e) {
                $e[0] = URLHelper::transform($e[0]);
            }
            $story->elements = $elements;
        }

        return response()->json($stories, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
