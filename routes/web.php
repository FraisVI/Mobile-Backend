<?php

use App\Http\Controllers\Admin\ArticlesController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\SegmentController;
use App\Http\Controllers\Admin\ShopsController;
use App\Http\Controllers\Admin\StoriesController;
use App\Http\Controllers\Admin\StoriesV2Controller;
use App\Http\Controllers\Admin\NotificationHistoryController;
use App\Http\Controllers\Admin\NotificationReportsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return '';
});

Route::prefix('/admin')
    ->namespace('App\Http\Controllers\Admin')
    ->middleware('admin')
    ->group(function() {
        // Для superadmin - доступ ко всем маршрутам
        Route::middleware('role:superadmin')->group(function() {
            Route::get('/', 'DashboardController@Show');
            Route::get('/settings', 'SettingsController@Show')->name('admin.settings');
            Route::post('/settings', 'SettingsController@update')->name('admin.settings.update');
            Route::get('/users', 'UsersController@Show');
            Route::get('/sessions', 'SessionsController@Show');
            Route::get('/notifications', 'NotificationsController@Show')->name('notification.show');
            Route::post('/notifications/send', 'NotificationsController@send')->name('notification.send');
            Route::get('/notifications/validate', 'NotificationsController@validate')->name('notification.validate');
            Route::get('/notification-history', [NotificationHistoryController::class, 'Show']);
            Route::get('/notification-reports', [NotificationReportsController::class, 'index'])->name('notification.reports');
            Route::get('/feedback', 'FeedbackController@index')->name('feedback.index');
            Route::get('/feedback/file/{filename}', 'FeedbackController@file');
            Route::get('/feedback/{user}', 'FeedbackController@Show')->name('feedback.show');
            Route::post('/feedback/{user}/send', 'FeedbackController@send')->name('feedback.send');
            Route::delete('/feedback/destroy/{id}', 'FeedbackController@destroy')->name('feedback.destroy');
            Route::resource('articles', ArticlesController::class)->only(['index', 'create', 'store', 'update', 'edit']);
            Route::resource('stories', StoriesController::class)->only(['index', 'create', 'store', 'update', 'edit', 'destroy']);
            Route::post('/stories/reorder', 'StoriesController@reorder')->name('stories.reorder');
            Route::resource('storiesV2', StoriesV2Controller::class)->only(['index', 'create', 'store', 'update', 'edit', 'destroy']);
            Route::post('/storiesV2/reorder', 'StoriesV2Controller@reorder')->name('storiesV2.reorder');
            Route::resource('shops', ShopsController::class)->only(['index', 'create', 'store', 'update', 'edit', 'destroy']);
            Route::resource('segments', SegmentController::class)->only(['index', 'create', 'store', 'update', 'edit', 'destroy']);
            Route::get('/segments/search-users', [SegmentController::class, 'searchUsers'])->name('segments.search-users');
            Route::get('/segments/{id}/list', [SegmentController::class, 'list'])->name('segments.list');
            Route::post('/segments/{id}/resubscribe', [SegmentController::class, 'resubscribe'])->name('segments.resubscribe');
            Route::get('/ratings', 'RatingController@Show')->name('rating.show');

            // Управление очередями
            Route::get('/queue/failed-jobs', [QueueController::class, 'failedJobs'])->name('queue.failed-jobs');
            Route::post('/queue/retry/{id}', [QueueController::class, 'retryJob'])->name('queue.retry-job');
            Route::post('/queue/retry-all', [QueueController::class, 'retryAll'])->name('queue.retry-all');
            Route::post('/queue/forget/{id}', [QueueController::class, 'forgetJob'])->name('queue.forget-job');
            Route::post('/queue/flush-all', [QueueController::class, 'flushAll'])->name('queue.flush-all');
        });

        // Для admin - доступ только к разблокировке пользователей
        Route::middleware('role:admin,superadmin')->group(function() {
            Route::get('/unban-user', 'UsersController@UnbanUserPage')->name('unban-user');
            Route::post('/unban-user', 'UsersController@UnbanUser')->name('unban-user.process');
        });
    });
