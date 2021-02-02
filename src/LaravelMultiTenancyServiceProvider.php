<?php

namespace Joaovdiasb\LaravelMultiTenancy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Joaovdiasb\LaravelMultiTenancy\Http\Middleware\TenancyChangeConnection;
use Illuminate\Contracts\Http\Kernel;

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
        $this->publishMigration();
        $this->routeMiddleware();

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

    public function routeMiddleware()
    {
        $middlewareClass = TenancyChangeConnection::class;

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('tenancy', $middlewareClass);

        $kernel = $this->app->make(Kernel::class);
        $kernel->prependToMiddlewarePriority($middlewareClass);
    }

    /**
     * Publish Migration
     *
     * @return void
     */
    public function publishMigration()
    {
        if ($this->app->runningInConsole()) {
            if (!class_exists('\database\migrations\tenancy\CreateTenancysTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_tenancys_table.php.stub' => database_path('migrations/tenancy/' . date('Y_m_d_His', time()) . '_create_tenancys_table.php'),
                ], 'migrations');
            }
        }
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
