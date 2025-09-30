<?php

namespace Octany\Providers;

use Illuminate\Support\ServiceProvider;

class OctanyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/octany-php.php',
            'octany-php'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/octany-php.php' => config_path('octany-php.php'),
        ], 'config');
    }
}
