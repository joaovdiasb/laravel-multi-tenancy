<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;

class TenancyMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:migrate {tenancy?} {--fresh} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenancy migrate';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->argument('tenancy')) {
            $this->migrate(
                Tenancy::find($this->argument('tenancy'))
            );
        } else {
            Tenancy::all()->each(
                fn ($tenancy) => $this->migrate($tenancy)
            );
        }
    }

    public function migrate($tenancy)
    {
        try {
            $tenancy->configure()->use();

            $this->line('');
            $this->line('-------------------------------------------');
            $this->line("Migrating Tenancy #{$tenancy->id} ({$tenancy->name})");
            $this->line('-------------------------------------------');

            DB::connection('tenancy')->getDatabaseName();

            if (
                App::environment('production') &&
                Schema::hasTable('users') &&
                !$this->confirm('The client has data, are you sure you want to continue?')
            ) {
                return $this->line('Action canceled.');
            }

            $options = ['--force' => true];

            if ($this->option('seed')) {
                $options['--seed'] = true;
            }

            $this->call(
                $this->option('fresh') ? 'migrate:fresh' : 'migrate',
                $options
            );

            if (config('tenancy.passport') && $this->option('fresh')) {
                $this->call('passport:client', [
                    '--personal' => true,
                    '--no-interaction' => true
                ]);
            }
        } catch (\Exception $e) {
            $this->info($e->getMessage());
        }
    }
}
