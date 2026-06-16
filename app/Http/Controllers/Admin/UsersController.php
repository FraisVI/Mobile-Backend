<?php

namespace App\Http\Controllers\Admin;

use App\Models\AppUser;
use App\Services\BanService;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class UsersController extends AdminController
{
    protected BanService $banService;

    public function __construct(BanService $banService)
    {
        parent::__construct();
        $this->banService = $banService;
    }

    public function Show(Request $request)
    {
        $title = 'Пользователи';
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 50;
        }
        $sort = $request->get('sort', 'id');
        $order = strtolower($request->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['id', 'lastname', 'firstname', 'email', 'phone', 'birthdate', 'gender', 'bonus_count', 'bonus_rate', 'sum_next_level', 'updated_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'id';
        }

        $users = AppUser::orderBy($sort, $order)->paginate($perPage)->withQueryString();

        return view('admin.users', compact('title', 'users', 'sort', 'order', 'perPage'));
    }

    public function UnbanUserPage()
    {
        return view('admin.unban-user');
    }

    public function UnbanUser(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $phone = $request->input('phone');

        try {
            $this->banService->unban(null, $phone);
        } catch (HttpResponseException $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при разблокировке: ' . $e->getResponse()->original['message'] ?? $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Пользователь успешно разбанен');
    }
}
