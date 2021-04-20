<?php

namespace Joaovdiasb\LaravelMultiTenancy\Traits;

trait TenantConfig
{
  public function tenantConnectionFullName(): string
  {
    return 'database.connections.' . $this->tenantConnectionName();
  }

  public function tenantConnectionName(): string
  {
    return config('tenant.tenant_connection_name') ?? config('database.default');
  }
}
