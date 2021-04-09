<?php

namespace App\Console\Commands;

use App\Models\Tenancy;

class TenancySeedCommand extends BaseCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'tenancy:seed {tenancy?} {--class=*}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Tenancy seed';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle(): int
  {
    try {
      $this->argument('tenancy')
        ? $this->migrate(Tenancy::find($this->argument('tenancy')))
        : Tenancy::all()->each(fn ($tenancy) => $this->migrate($tenancy));
    } catch (\Exception $e) {
      $this->tenancy->configureBack()->use();
      $this->error($e->getMessage());

      return 1;
    }

    return 0;
  }

  public function seed($tenancy)
  {
    $this->tenancy = $tenancy;

    $tenancy->configure()->use();

    $this->lineHeader("Seeding Tenancy #{$tenancy->id} ({$tenancy->name})");

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
