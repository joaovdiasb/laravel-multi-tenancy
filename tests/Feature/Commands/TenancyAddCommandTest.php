<?php

namespace Joaovdiasb\LaravelMultiTenancy\Tests\Feature\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Joaovdiasb\LaravelMultiTenancy\Tests\TestCase;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenantException;

class TenantAddCommandTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    $this->commandParams = [
      'name'        => Str::random(10),
      'reference'   => Str::random(10),
      'db_host'     => config('database.connections.tenant.host'),
      'db_port'     => config('database.connections.tenant.port'),
      'db_name'     => 'test' . Str::uuid()->getHex(),
      'db_user'     => config('database.connections.tenant.username'),
      'db_password' => config('database.connections.tenant.password')
    ];
  }

  private function clearTest(Model $tenant): void
  {
    DB::connection('tenant')->statement("DROP DATABASE {$tenant->db_name}");
    $tenant->configureBack()->use();
  }

  /** @test */
  public function it_add_a_tenant_and_create_database()
  {
    $this->artisan('tenant:add', $this->commandParams)
      ->assertExitCode(0);

    $tenant = Tenant::latest()->first();
    $tenant->configure()->use();

    $this->assertNotEmpty($tenant);
    $this->assertTrue(DB::connection('tenant')->getDatabaseName() === $tenant->db_name);

    $this->clearTest($tenant);
  }

  /** @test */
  public function it_return_to_original_connection_and_delete_tenant_when_fail_create_database_with_not_allowed_name()
  {
    $beforeConn = config('database.connections.tenant');
    $this->commandParams['db_name'] = '000';

    $this->artisan('tenant:add', $this->commandParams)
      ->assertExitCode(1);

    $diff = array_diff_assoc($beforeConn, config('database.connections.tenant'));
    $this->assertEmpty($diff);

    try {
      $this->assertEmpty(Tenant::findFirstByKey('reference', $this->commandParams['reference']));
    } catch (TenantException $e) {
      $this->assertNotEmpty($e);
    };
  }
}
