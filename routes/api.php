<?php

use Illuminate\Support\Facades\Route;

use Xerointegration\LaravelXero\Http\Controllers\XeroController;

Route::prefix('xero')->group(function () {

    Route::get(
        'connect',
        [XeroController::class, 'connect']
    );

    Route::get(
        'redirect',
        [XeroController::class, 'callback']
    );

    Route::get(
        'status',
        [XeroController::class, 'checkXeroStatus']
    );

    Route::post(
        'disconnect',
        [XeroController::class, 'disconnectXero']
    );

    Route::post(
        'webhook',
        [XeroController::class, 'handleWebhook']
    );

    Route::post(
        'sync',
        [XeroController::class, 'syncOnXero']
    );
});