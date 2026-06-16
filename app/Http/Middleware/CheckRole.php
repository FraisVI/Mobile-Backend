<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $admin = Admin::findAdmin($request->getUser(), $request->getPassword());

        if (!$admin) {
            $headers = array('WWW-Authenticate' => 'Basic');
            return response('Admin Login', 401, $headers);
        }

        if ($admin->role === 'admin' && $request->path() !== 'admin/unban-user') {
            return redirect()->route('unban-user' );
        }

        return $next($request);
    }
}
