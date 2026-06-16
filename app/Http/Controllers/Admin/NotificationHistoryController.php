<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationHistoryController extends AdminController
{
    public function Show(Request $request)
    {
        $title = 'История рассылок';
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 50;
        }
        $sort = $request->get('sort', 'time');
        $order = strtolower($request->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['time', 'title', 'message', 'total', 'sent'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'time';
        }

        $logs = DB::table('notification_sent_log')
            ->orderBy($sort, $order)
            ->paginate($perPage)
            ->withQueryString();

        foreach ($logs as $l) {
            $l->time = \Carbon\Carbon::parse($l->time);
            $l->read = DB::table('article_view_log')->where('article_id', $l->article_id)->count();
        }

        return view('admin.notification-history', compact('title', 'logs', 'sort', 'order', 'perPage'));
    }
}
