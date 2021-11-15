<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Illuminate\Support\Facades\DB;

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

  private Tenant $tenant;

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle(): int
  {
    DB::beginTransaction();

    try {
      $this->argument('tenant')
        ? $this->seed(Tenant::find($this->argument('tenant')))
        : Tenant::all()->each(fn ($tenant) => $this->seed($tenant));
    } catch (\Exception $e) {
      $this->tenant->restore();
      DB::rollback();
      $this->error($e->getMessage());

      return 1;
    }

    DB::commit();

    return 0;
  }

  public function seed($tenant): void
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

    $tenant->restore();
  }
}
