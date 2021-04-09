<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;

class TenancyMigrateCommand extends BaseCommand
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

    public function migrate($tenancy): void
    {
        $this->tenancy = $tenancy;

        $tenancy->configure()->use();

        DB::connection('tenancy')->getDatabaseName();

        $this->lineHeader("Migrating Tenancy #{$tenancy->id} ({$tenancy->name})");

        if (
            App::environment('production') &&
            Schema::hasTable('migrations') &&
            !$this->confirm('The client has data, are you sure you want to continue?')
        ) {
            throw new \Exception('Action canceled.');
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

        $tenancy->configureBack()->use();
    }
}
