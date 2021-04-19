<?php

namespace Joaovdiasb\LaravelMultiTenancy\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Joaovdiasb\LaravelMultiTenancy\LaravelMultiTenancyServiceProvider;

class TestCase extends BaseTestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->setUpDatabase();
        // $this->artisan('migrate', ['--database' => 'tenancy']);
        // $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
        // $this->loadLaravelMigrations(['--database' => 'tenancy']);
        // $this->beforeApplicationDestroyed(function () {
        //     $this->artisan('migrate:rollback');
        // });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->bind('DatabaseSeeder', 'Joaovdiasb\LaravelMultiTenancy\Tests\MockDatabaseSeeder');
        $app['config']->set('tenancy', [
            'encrypt_key' => '318654690878bef944a8b542ddb55d82',
            'database'    => 'mysql',
            'current_container_key' => 'currentTenancy',
            'connection_name' => 'tenancy',
        ]);
        $app['config']->set('database.connections.tenancy', [
            'driver'   => env('DB_DRIVER'),
            'database' => env('DB_DATABASE'),
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD')
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [LaravelMultiTenancyServiceProvider::class];
    }

    protected function setUpDatabase()
    {
        include_once __DIR__ . '/../database/migrations/create_tenancys_table.php.stub';
        (new \CreateTenancysTable())->down();
        (new \CreateTenancysTable())->up();
    }
}
