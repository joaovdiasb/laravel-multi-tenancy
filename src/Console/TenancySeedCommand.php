<?php

namespace App\Console\Commands;

use App\Models\Tenancy;
use Illuminate\Console\Command;

class TenancySeedCommand extends Command
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
  public function handle()
  {
    if ($this->argument('tenant')) {
      $this->seed(
        Tenancy::find($this->argument('tenant'))
      );
    } else {
      Tenancy::all()->each(
        fn ($tenant) => $this->seed($tenant)
      );
    }
  }

  public function seed($tenant)
  {
    try {
      $tenant->configure()->use();

      $this->line('');
      $this->line('-------------------------------------------');
      $this->line("Seeding Tenancy #{$tenant->id} ({$tenant->name})");
      $this->line('-------------------------------------------');

      $options = ['--force' => true];

      if ($this->option('class')) {
        $options['--class'] = $this->option('class')[0];
      }

      $this->call(
        'db:seed',
        $options
      );
    } catch (\Exception $e) {
      $this->info($e->getMessage());
    }
  }
}
