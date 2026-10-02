<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SlugGenerator;

class SlugServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(SlugGenerator::class , function($app){
            return new SlugGenerator();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
