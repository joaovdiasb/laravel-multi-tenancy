<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use App\Models\Tenant;

class TenantSeedCommand extends BaseCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'tenant:seed {tenant?} {--class=*}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Tenant seed';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle(): int
  {
    try {
      $this->argument('tenant')
        ? $this->migrate(Tenant::find($this->argument('tenant')))
        : Tenant::all()->each(fn ($tenant) => $this->migrate($tenant));
    } catch (\Exception $e) {
      $this->tenant->restore();
      $this->error($e->getMessage());

      return 1;
    }

    return 0;
  }

  public function seed($tenant)
  {
    $this->tenant = $tenant;

    $tenant->configure()->use();

    $this->lineHeader("Seeding Tenant #{$tenant->id} ({$tenant->name})");

    $options = ['--force' => true];

    if ($this->option('class')) {
      $options['--class'] = $this->option('class')[0];
    }

    $this->call(
      'db:seed',
      $options
    );
  }
}
