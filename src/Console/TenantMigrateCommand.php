<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Support\Facades\{App, DB};
use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Joaovdiasb\LaravelMultiTenancy\Traits\MultitenancyConfig;

class TenantMigrateCommand extends BaseCommand
{
    use MultitenancyConfig;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate {tenant?} {--fresh} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenant migrate';

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

    public function migrate($tenant): void
    {
        $this->tenant = $tenant;

        $tenant->configure()->use();

        $this->lineHeader("Migrating Tenant #{$tenant->id} ({$tenant->name})");

        $databaseHasData = DB::connection($this->tenantConnectionName())
            ->getDoctrineSchemaManager()
            ->listTableNames();

        if (
            $databaseHasData &&
            App::environment('production') &&
            !$this->confirm("The client has data, are you sure you want to continue?")
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

        if (config('multitenancy.passport') && $this->option('fresh')) {
            $this->call('passport:client', [
                '--personal' => true,
                '--no-interaction' => true
            ]);
        }

        $tenant->restore();
    }
}
