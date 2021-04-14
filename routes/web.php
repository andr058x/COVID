<?php

use App\Http\Controllers\Ajax;
use App\Http\Controllers\Webhooks;
use Illuminate\Support\Facades\Route;

Route::view('/', 'app');

Route::name('ajax.')->prefix('ajax')->group(function () {
    Route::get('metrics', Ajax\MetricsController::class)->name('metrics');
});

Route::name('webhooks.')->prefix('webhooks')->group(function () {
    Route::post('telegram', Webhooks\TelegramController::class)->name('telegram');
});
