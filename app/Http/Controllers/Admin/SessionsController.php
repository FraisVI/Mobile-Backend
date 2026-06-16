<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\AppUser;
use App\Models\Session;
use Illuminate\Http\Request;

class SessionsController extends AdminController
{
    public function Show(Request $request)
    {
        $title = 'Сессии пользователей';
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 50;
        }
        $sort = $request->get('sort', 'lastused');
        $order = strtolower($request->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['lastused', 'user_id', 'authorized', 'registered', 'version'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'lastused';
        }

        $sessions = Session::where('user_id', '>', 0)
            ->orderBy($sort, $order)
            ->paginate($perPage)
            ->withQueryString();

        $userIds = $sessions->pluck('user_id')->unique()->filter()->values()->all();
        $users = $userIds ? AppUser::whereIn('id', $userIds)->get()->keyBy('id') : collect();

        return view('admin.sessions', compact('title', 'sessions', 'users', 'sort', 'order', 'perPage'));
    }
}
