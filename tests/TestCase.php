<?php

namespace Joaovdiasb\LaravelMultiTenancy\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Joaovdiasb\LaravelMultiTenancy\LaravelMultiTenancyServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setup(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->setUpDatabase();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->bind('DatabaseSeeder', 'Joaovdiasb\LaravelMultiTenancy\Tests\MockDatabaseSeeder');
        $app['config']->set('tenant', [
            'encrypt_key' => '318654690878bef944a8b542ddb55d82',
            'database'    => 'mysql',
            'current_container_key' => 'currentTenant',
            'tenant_connection_name' => 'tenant',
            'landlord_connection_name' => 'landlord',
        ]);
        $app['config']->set('database.connections.tenant', [
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
        include_once __DIR__ . '/../database/migrations/create_tenants_table.php.stub';
        (new \CreateTenantsTable())->down();
        (new \CreateTenantsTable())->up();
    }
}
