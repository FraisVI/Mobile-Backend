<?php

use App\Http\Controllers\ServiceApi\CallCodeController;
use App\Http\Controllers\ServiceApi\NotificationController;
use App\Http\Controllers\ServiceApi\RatingController;
use App\Http\Controllers\ServiceApi\SegmentController;
use App\Http\Controllers\ServiceApi\SessionController;
use App\Http\Controllers\ServiceApi\ChangePhoneController;
use Illuminate\Support\Facades\Route;

Route::get('/sessions/active', [SessionController::class, 'active']);
Route::get('/callcode', [CallCodeController::class, 'list']);
Route::post('/callcode/validate', [CallCodeController::class, 'validate']);

Route::post('/notification/send', [NotificationController::class, 'send']);

Route::post('/rating/create', [RatingController::class, 'create']);
Route::post('/rating/completed', [RatingController::class, 'completed']);

Route::post('/segment/push', [SegmentController::class, 'push']);
Route::post('/change-phone', ChangePhoneController::class);
