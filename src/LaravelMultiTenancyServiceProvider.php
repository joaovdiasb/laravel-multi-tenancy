<?php

namespace Joaovdiasb\LaravelMultiTenancy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Joaovdiasb\LaravelMultiTenancy\Http\Middleware\TenantChangeConnection;
use Illuminate\Contracts\Http\Kernel;
use Joaovdiasb\LaravelMultiTenancy\Console\{TenantAddCommand, TenantBackupCommand, TenantMigrateCommand};

class LaravelMultiTenancyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/multitenancy.php', 'laravel-multi-tenancy');
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
     * Add route middleware and set priority.
     *
     * @return void
     */
    public function routeMiddleware(): void
    {
        $middlewareClass = TenantChangeConnection::class;

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('multitenancy', $middlewareClass);

        $kernel = $this->app->make(Kernel::class);
        $kernel->prependToMiddlewarePriority($middlewareClass);
    }

    /**
     * Publish migration.
     *
     * @return void
     */
    public function publishMigration(): void
    {
        if ($this->app->runningInConsole()) {
            if (!class_exists('app\database\migrations\tenant\CreateTenantsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_tenants_table.php.stub' => database_path('migrations/tenant/' . date('Y_m_d_His', time()) . '_create_tenants_table.php'),
                ], 'migrations');
            }
        }
    }

    /**
     * Publish config.
     *
     * @return void
     */
    public function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/multitenancy.php' => config_path('multitenancy.php'),
            ], 'config');
        }
    }

    /**
     * Register commands.
     *
     * @return void
     */
    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TenantAddCommand::class,
                TenantMigrateCommand::class,
                TenantBackupCommand::class
            ]);
        }
    }
}
