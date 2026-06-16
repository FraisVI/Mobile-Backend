<?php

namespace App\Http\Middleware;

use App\Services\BanService;
use Closure;

class BanCheck
{
    protected $ban;

    public function __construct(BanService $ban)
    {
        $this->ban = $ban;
    }

    public function handle($request, Closure $next)
    {
        $appVersion = $request->header('X-App-Version');
        $ip = $request->ip();
        $phone = '+7' . ltrim($request->json('phone'), '7');

        $this->ban->checkAndRecord($appVersion, $ip, $phone);

        return $next($request);
    }
}
