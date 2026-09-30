<?php

namespace SotersFolio\RexPay\Providers;

if (class_exists('Illuminate\\Support\\ServiceProvider')) {
    class RexPayServiceProvider extends \Illuminate\Support\ServiceProvider
    {
        public function register()
        {
            $this->mergeConfigFrom(__DIR__ . '/../../config/rexpay.php', 'rexpay');

            $this->app->singleton('rexpay', function ($app) {
                $config = $app['config']['rexpay'];
                return new \SotersFolio\RexPay\RexPay(
                    env('REXPAY_USERNAME', ''),
                    env('REXPAY_SECRET_KEY', ''),
                    $config['mode'] ?? 'test'
                );
            });
        }

        public function boot()
        {
            $this->publishes([
                __DIR__ . '/../../config/rexpay.php' => config_path('rexpay.php'),
            ], 'rexpay-config');
        }
    }
}
