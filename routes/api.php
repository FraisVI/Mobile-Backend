<?php

use App\Http\Controllers\Api\AccountDeleteController;
use App\Http\Controllers\Api\CallCodeController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\StoriesController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\OrderHistoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\PushEventController;
use App\Http\Controllers\FirebaseTopicController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [LoginController::class, 'AppLogin'])
    ->middleware('throttle:10,1')
//    ->middleware('block.by.country')
    ->middleware('smart.captcha')
    ->middleware('ban.check');

Route::post('/login/verify', [LoginController::class, 'AppLoginVerify']);
Route::post('/login/resend', [LoginController::class, 'AppLoginResend']);
Route::post('/logout', [LoginController::class, 'AppLogout']);
Route::post('/register', [LoginController::class, 'AppRegister']);

Route::get('/promotions', [ArticleController::class, 'Promotions']);
Route::get('/article/{id}', [ArticleController::class, 'Show']);

Route::get('/shops', [ShopController::class, 'List']);
Route::get('/notifications', [NotificationController::class, 'Notifications']);

Route::get('/feedbacks', [FeedbackController::class, 'Feedbacks']);
Route::post('/feedback/create', [FeedbackController::class, 'Create']);

Route::get('/stories', [StoriesController::class, 'List']);
Route::get('/storiesV2', [StoriesController::class, 'ListV2']);

Route::get('/callcode', [CallCodeController::class, 'Create']);

Route::get('/orders', [OrderHistoryController::class, 'List']);

Route::get('/ratevisit/{hash}', [RatingController::class, 'get'])->name('rating.get');
Route::post('/ratevisit/submit', [RatingController::class, 'submit'])->name('rating.submit');

Route::get('/account-delete/status', [AccountDeleteController::class, 'status']);
Route::get('/account-delete/initiate', [AccountDeleteController::class, 'initiate']);
Route::post('/account-delete/confirm', [AccountDeleteController::class, 'confirm']);


Route::post('/push-event', [PushEventController::class, 'store']);

Route::middleware('log.profile')->group(function () {
    Route::get('/profile', [ProfileController::class, 'ProfileInformation']);
    Route::post('/profile/fcm', [ProfileController::class, 'SetFcmToken']);
    Route::post('/profile/set/fio', [ProfileController::class, 'SetFio']);
    Route::post('/profile/set/email', [ProfileController::class, 'SetEmail']);
    Route::post('/profile/set/gender', [ProfileController::class, 'SetGender']);
    Route::post('/profile/set/check', [ProfileController::class, 'SetCheck']);
    Route::post('/profile/set/notify', [ProfileController::class, 'SetNotify']);
});
