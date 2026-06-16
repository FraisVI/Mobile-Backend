<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        $admin = Admin::findAdmin($request->getUser(), $request->getPassword());

        if (!$admin == null) {
            if (!$admin->exists()) {
                $headers = array('WWW-Authenticate' => 'Basic');
                return response('Admin Login', 401, $headers);
            }
        }

        return $next($request);
    }
}
