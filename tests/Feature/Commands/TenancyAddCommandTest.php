<?php

namespace Joaovdiasb\LaravelMultiTenancy\Tests\Feature\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;
use Joaovdiasb\LaravelMultiTenancy\Tests\TestCase;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyException;

class TenancyAddCommandTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    $this->commandParams = [
      'name'        => Str::random(10),
      'reference'   => Str::random(10),
      'db_host'     => config('database.connections.tenancy.host'),
      'db_port'     => config('database.connections.tenancy.port'),
      'db_name'     => 'test' . Str::uuid()->getHex(),
      'db_user'     => config('database.connections.tenancy.username'),
      'db_password' => config('database.connections.tenancy.password')
    ];
  }

  private function clearTest(Model $tenancy): void
  {
    DB::connection('tenancy')->statement("DROP DATABASE {$tenancy->db_name}");
    $tenancy->configureBack()->use();
  }

  /** @test */
  public function it_add_a_tenancy_and_create_database()
  {
    $this->artisan('tenancy:add', $this->commandParams)
      ->assertExitCode(0);

    $tenancy = Tenancy::latest()->first();
    $tenancy->configure()->use();

    $this->assertNotEmpty($tenancy);
    $this->assertTrue(DB::connection('tenancy')->getDatabaseName() === $tenancy->db_name);

    $this->clearTest($tenancy);
  }

  /** @test */
  public function it_return_to_original_connection_and_delete_tenancy_when_fail_create_database_with_not_allowed_name()
  {
    $beforeConn = config('database.connections.tenancy');
    $this->commandParams['db_name'] = '000';

    $this->artisan('tenancy:add', $this->commandParams)
      ->assertExitCode(1);

    $diff = array_diff_assoc($beforeConn, config('database.connections.tenancy'));
    $this->assertEmpty($diff);

    try {
      $this->assertEmpty(Tenancy::findFirstByKey('reference', $this->commandParams['reference']));
    } catch (TenancyException $e) {
      $this->assertNotEmpty($e);
    };
  }
}
