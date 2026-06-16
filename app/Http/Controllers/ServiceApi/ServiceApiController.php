<?php

namespace App\Http\Controllers\ServiceApi;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ServiceApiController extends BaseController
{
    protected const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            if (!$request->hasHeader('Authorization'))
                return $this->ResponseUnauthorized();

            $apikey = $request->header('Authorization');

            if ($apikey != env('API_KEY'))
                return $this->ResponseUnauthorized();

            return $next($request);
        });
    }

    protected function ResponseUnauthorized(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }
}
