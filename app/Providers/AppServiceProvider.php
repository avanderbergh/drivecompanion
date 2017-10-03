<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('userservice', function () {
            return new \App\Services\UserService;
        });
        $this->app->bind('sectionservice', function () {
            return new \App\Services\SectionService;
        });
        $this->app->bind('assignmentservice', function () {
            return new \App\Services\AssignmentService;
        });
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);
    }
}
