<?php

namespace App\Providers;

use App\Services\PlanResolver;
use Illuminate\Support\ServiceProvider;

class PlanServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('planresolver', function () {
           return new PlanResolver();
        });
    }

    public function boot()
    {
        //
    }
}