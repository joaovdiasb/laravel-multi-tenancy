<?php

namespace Joaovdiasb\LaravelMultiTenancy\Tests\Feature\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Joaovdiasb\LaravelMultiTenancy\Tests\TestCase;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenantException;
use Joaovdiasb\LaravelMultiTenancy\Traits\MultitenancyConfig;

class TenantAddCommandTest extends TestCase
{
  use MultitenancyConfig;

  public function setUp(): void
  {
    parent::setUp();

    $this->commandParams = [
      'name'        => Str::random(10),
      'reference'   => Str::random(10),
      'db_host'     => config($this->landlordConnectionFullName() . '.host'),
      'db_port'     => config($this->landlordConnectionFullName() . '.port'),
      'db_name'     => 'test' . Str::uuid()->getHex(),
      'db_user'     => config($this->landlordConnectionFullName() . '.username'),
      'db_password' => config($this->landlordConnectionFullName() . '.password'),
      'driver'      => ''
    ];
  }

  private function clearTest(Model $tenant): void
  {
    DB::connection($this->tenantConnectionName())->statement("DROP DATABASE {$tenant->db_name}");
    $tenant->restore();
  }

  /** @test */
  public function it_create_database_and_tenant_after_command_runs_successfully()
  {
    $this->artisan('tenant:add', $this->commandParams)
      ->assertExitCode(0);

    $tenant = (new Tenant())->latest()->first()->configure()->use();
    $this->assertTrue($tenant->id === Tenant::current()->id);
    $this->assertTrue(
      config($this->tenantConnectionFullName() . '.database') === $tenant->db_name
    );

    $this->clearTest($tenant);
  }

  /** @test */
  public function it_delete_tenant_when_fail_create_database_with_not_allowed_name()
  {
    $this->commandParams['db_name'] = '000';

    $this->artisan('tenant:add', $this->commandParams)
      ->assertExitCode(1);

    $this->expectException(TenantException::class);

    try {
      Tenant::findFirstByKey('reference', $this->commandParams['reference']);
    } catch (TenantException $e) {
      throw $e;
    }
  }

  /** @test */
  public function it_dont_change_landlord_connection_after_command_runs_successfully()
  {
    $beforeConn = config($this->landlordConnectionFullName());

    $this->artisan('tenant:add', $this->commandParams)
      ->assertExitCode(0);

    $tenant = (new Tenant())->latest()->first()->configure()->use();
    $this->assertNotEmpty($tenant);

    $diff = array_diff_assoc($beforeConn, config($this->landlordConnectionFullName()));
    $this->assertEmpty($diff);

    $this->clearTest($tenant);
  }
}
