<?php

namespace Octany\Providers;

use Illuminate\Support\ServiceProvider;
use Octany\OctanyClient;

class OctanyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/octany-php.php',
            'octany-php'
        );

        $this->app->singleton(OctanyClient::class, function ($app) {
            $config = $app['config']['octany-php'] ?? [];

            $options = [];

            if (! empty($config['api_url'])) {
                $options['domain'] = $config['api_url'];
            }

            if (! empty($config['http_options'])) {
                $options['http_options'] = $config['http_options'];
            }

            return new OctanyClient(
                $config['account'] ?? null,
                $config['api_key'] ?? null,
                $options
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/octany-php.php' => config_path('octany-php.php'),
        ], 'config');
    }
}
