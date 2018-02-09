<?php

namespace Macgriog\Fractal;

use Illuminate\Support\ServiceProvider;

class FractalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('fractal', function (...$arguments) {
            return Fractal::create(...$arguments);
        });
        $this->app->alias('fractal', Fractal::class);
    }
}
