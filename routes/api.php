<?php

use Illuminate\Support\Facades\Route;

use Xerointegration\LaravelXero\Http\Controllers\XeroController;

Route::prefix('api/xero')->group(function () {

    Route::get(
        'connect',
        [XeroController::class, 'connect']
    );

    Route::get(
        'redirect',
        [XeroController::class, 'callback']
    );
});