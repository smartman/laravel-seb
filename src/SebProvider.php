<?php

namespace Smartman\Seb;

use Illuminate\Support\ServiceProvider;

class SebProvider extends ServiceProvider
{

    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/seb.php' => config_path('seb.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton('seb-bank', function ($app) {
            return new SebImplementation($app);
        });
    }

    public function provides()
    {
        return ['seb-bank'];
    }
}
