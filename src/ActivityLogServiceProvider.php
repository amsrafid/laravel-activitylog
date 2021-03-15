<?php

namespace Amsrafid\ActivityLog;

use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/activitylog.php' => config_path('activitylog.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/activitylog.php', 'activitylog'
        );

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
