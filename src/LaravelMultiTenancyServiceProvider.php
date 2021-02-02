<?php

namespace Joaovdiasb\LaravelMultiTenancy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class LaravelMultiTenancyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/LaravelMultiTenancy.php', 'laravel-multi-tenancy');

        $this->publishConfig();

        // $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-multi-tenancy');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        });
    }

    /**
    * Get route group configuration array.
    *
    * @return array
    */
    private function routeConfiguration()
    {
        return [
            'namespace'  => "Joaovdiasb\LaravelMultiTenancy\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register facade
        $this->app->singleton('laravel-multi-tenancy', function () {
            return new LaravelMultiTenancy;
        });
    }

    /**
     * Publish Config
     *
     * @return void
     */
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/LaravelMultiTenancy.php' => config_path('LaravelMultiTenancy.php'),
            ], 'config');
        }
    }
}
