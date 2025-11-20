<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
{
    $this->app->bind('App\Services\ReportService', function () {
        return new \App\Services\ReportService();
    });
}


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
