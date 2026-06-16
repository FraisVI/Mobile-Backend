<?php

namespace App\Http\Controllers\Api;

use App\Facades\URLHelper;
use App\Models\City;
use App\Models\Shop;

class ShopController extends ApiController
{
    public function List(): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\Shop[] $shops */
        $shops = Shop::all();
        $cities = City::orderBy('name')->get();
        foreach ($shops as &$shop) {
            if ($shop->images) {
                $images = [];
                foreach ($shop->images as $i) {
                    $images[] = URLHelper::transform($i);
                }
                $shop->images = $images;
            }
        }

        return response()->json([
            'shops' => $shops,
            'cities' => $cities,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
