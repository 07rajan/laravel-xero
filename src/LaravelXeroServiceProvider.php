<?php

namespace Xerointegration\LaravelXero;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelXeroServiceProvider
    extends ServiceProvider
{
    public function boot()
    {
        Route::middleware('api')
            ->group(
                __DIR__.'/../routes/api.php'
            );

        $this->loadMigrationsFrom(
            __DIR__.'/../database/migrations'
        );

        $this->publishes([
            __DIR__.'/../config/xero.php' =>
                config_path('xero.php'),
        ], 'xero-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/xero.php',
            'xero'
        );
    }
}