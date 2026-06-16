<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feedback;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class AdminController extends BaseController
{
    const FEEDBACK_COUNTER_CACHE_TTL = 120;
    var array $menu = [
        '/'          => 'Dashboard',
        '/users'     => 'Пользователи',
        '/sessions'  => 'Сессии',
        '/shops'     => 'Магазины',
        '/articles'  => 'Акции и Новости',
        '/stories'   => 'Stories',
        '/storiesV2' => 'Stories v.2',
        '/feedback'  => 'Обратная связь',
        '/ratings'   => 'Оценки',
        '/segments'  => 'Сегменты пользователей',
        '/notifications' => 'Рекламные рассылки',
        '/notification-history' => 'История рассылок',
        '/notification-reports' => 'Отчёты по рассылкам',
        '/queue/failed-jobs' => 'Невыполненные задачи',
        '/settings'  => 'Параметры',
        '/unban-user' => 'Разблокировать пользователя',
    ];

    var array $menu_icons = [
        '/'            => 'fa-home',
        '/users'       => 'fa-users',
        '/sessions'    => 'fa-rss',
        '/shops'       => 'fa-shopping-cart',
        '/articles'    => 'fa-bolt',
        '/stories'     => 'fa-mobile',
        '/storiesV2'   => 'fa-mobile',
        '/feedback'    => 'fa-comments',
        '/ratings'     => 'fa-star',
        '/segments'    => 'fa-pie-chart',
        '/notifications'   => 'fa-bullhorn',
        '/notification-history' => 'fa-history',
        '/notification-reports' => 'fa-bar-chart',
        '/queue/failed-jobs' => 'fa-exclamation-triangle',
        '/settings'    => 'fa-cogs',
        '/unban-user' => 'fa-unlock',
    ];

    public function __construct()
    {
        $route = Request::route();
        $path = "/";
        if ($route) {
            $path = str_replace(Request::route()->getPrefix(), '', '/' . request()->path());
            if ($path == "") {
                $path = "/";
            }
        }

        $feedback_counter = Cache::remember('admin_feedback_counter', self::FEEDBACK_COUNTER_CACHE_TTL, function () {
            $ids = DB::table('feedback')->select([DB::raw('MAX(id) as id')])->groupBy('user_id')->pluck('id');
            if ($ids->isEmpty()) {
                return 0;
            }
            return Feedback::whereIn('id', $ids)->where('from_user', 1)->where('viewed', 0)->count();
        });

        view()->share('prefix', (($route != null) ? $route->getPrefix() : ''));
        view()->share('title', '');
        view()->share('menu', $this->menu);
        view()->share('menu_icons', $this->menu_icons);
        view()->share('feedback_counter', $feedback_counter);
        view()->share('path', $path);

        view()->share('route_name', Route::currentRouteName());
    }

    public static function getFileExtension($type): string
    {
        $ext = '.jpg';

        if ($type == 'image/jpeg') {
            $ext = '.jpg';
        } else if ($type == 'image/png') {
            $ext = '.png';
        } else if ($type == 'image/gif') {
            $ext = '.gif';
        }

        return $ext;
    }

    protected function storeFile(\Illuminate\Http\Request $request, $field): string
    {
        $file = $request->file($field);
        $ext = $this->getFileExtension($file->getMimeType());
        $filename = md5_file($file->getPathname()) . $ext;
        $file->storeAs('', $filename);

        return $filename;
    }
}
