<?php

namespace Joaovdiasb\LaravelMultiTenancy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Joaovdiasb\LaravelMultiTenancy\Http\Middleware\TenancyChangeConnection;
use Illuminate\Contracts\Http\Kernel;
use Joaovdiasb\LaravelMultiTenancy\Console\TenancyAddCommand;
use Joaovdiasb\LaravelMultiTenancy\Console\TenancyMigrateCommand;

class LaravelMultiTenancyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tenancy.php', 'laravel-multi-tenancy');
        $this->publishConfig();
        $this->publishMigration();
        $this->routeMiddleware();
        $this->registerCommands();

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
     * Route middleware.
     *
     * @return void
     */
    public function routeMiddleware(): void
    {
        $middlewareClass = TenancyChangeConnection::class;

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('tenancy', $middlewareClass);

        $kernel = $this->app->make(Kernel::class);
        $kernel->prependToMiddlewarePriority($middlewareClass);
    }

    /**
     * Publish migration
     *
     * @return void
     */
    public function publishMigration(): void
    {
        if ($this->app->runningInConsole()) {
            if (!class_exists('app\database\migrations\tenancy\CreateTenancysTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_tenancys_table.php.stub' => database_path('migrations/tenancy/' . date('Y_m_d_His', time()) . '_create_tenancys_table.php'),
                ], 'migrations');
            }
        }
    }

    /**
     * Publish config
     *
     * @return void
     */
    public function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tenancy.php' => config_path('tenancy.php'),
            ], 'config');
        }
    }

    /**
     * Register commands
     *
     * @return void
     */
    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TenancyAddCommand::class,
                TenancyMigrateCommand::class
            ]);
        }
    }
}
