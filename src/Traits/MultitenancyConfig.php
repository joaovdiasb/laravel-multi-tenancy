<?php

namespace Joaovdiasb\LaravelMultiTenancy\Traits;

trait MultitenancyConfig
{
  public function tenantConnectionFullName(): string
  {
    return 'database.connections.' . $this->tenantConnectionName();
  }

  public function tenantConnectionName(): string
  {
    return config('multitenancy.tenant_connection_name');
  }

  public function landlordConnectionFullName(): string
  {
    return 'database.connections.' . $this->landlordConnectionName();
  }

  public function landlordConnectionName(): string
  {
    return config('multitenancy.landlord_connection_name') ?? config('database.default');
  }
}
