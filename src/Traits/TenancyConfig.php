<?php

namespace Joaovdiasb\LaravelMultiTenancy\Traits;

trait TenancyConfig
{
  public function tenancyConnectionPath(): string
  {
    return 'database.connections.' . $this->tenancyConnectionName();
  }

  public function tenancyConnectionName(): string
  {
    return config('tenancy.connection_name') ?? config('database.default');
  }
}
