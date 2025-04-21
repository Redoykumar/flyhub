<?php

namespace Redoy\FlyHub;

use Illuminate\Support\ServiceProvider;
use Redoy\FlyHub\Core\FlyHubManager;

class FlyHubServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '../../Config/flyhub.php' => config_path('flyhub.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '../../Config/flyhub.php', 'flyhub');
        $this->app->singleton('flyhub', function () {
            return new FlyHubManager();
        });
    }
}