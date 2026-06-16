<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends AdminController
{
    public function Show()
    {
        $title = 'Настройки';
        $notificationsPruneLimit = Setting::get(Setting::NOTIFICATIONS_PRUNE_LIMIT, '10');

        return view('admin.settings', compact('title', 'notificationsPruneLimit'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notifications_prune_limit' => 'required|integer|min:0|max:1000',
        ], [
            'notifications_prune_limit.required' => 'Укажите количество сообщений.',
            'notifications_prune_limit.integer'  => 'Должно быть целое число.',
            'notifications_prune_limit.min'      => 'Значение не может быть меньше 0.',
            'notifications_prune_limit.max'      => 'Значение не может быть больше 1000.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.settings')
                ->withErrors($validator)
                ->withInput();
        }

        Setting::set(Setting::NOTIFICATIONS_PRUNE_LIMIT, $request->input('notifications_prune_limit'));

        return redirect()->route('admin.settings')->with('success', 'Настройки сохранены.');
    }
}
