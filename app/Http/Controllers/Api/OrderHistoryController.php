<?php

namespace App\Http\Controllers\Api;

use App\CardService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OrderHistoryController extends ApiController
{
    public function List(CardService $cardService): \Illuminate\Http\JsonResponse
    {
        $orders = $cardService->GetSaleHistory([
            'clientcardid' => $this->appUser->client_card_id,
        ]);

        $orders = $orders->history;
        foreach ($orders as &$order) {
            $order['date'] = Str::replace('T', ' ', $order['date']);
            $order['date'] = Carbon::parse($order['date'])->format('d.m.Y');
        }

        return response()->json($orders, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
