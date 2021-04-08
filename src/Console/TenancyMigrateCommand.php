<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Validator};
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
        try {
            if ($this->argument('tenancy')) {
                $this->migrate(
                    Tenancy::find($this->argument('tenancy'))
                );
            } else {
                Tenancy::all()->each(
                    fn ($tenancy) => $this->migrate($tenancy)
                );
            }
        } catch (\Exception $e) {
            $this->tenancy->configureBack()->use();
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    private function lineHeader(string $message): void
    {
        $this->line('');
        $this->line('-------------------------------------------');
        $this->line($message);
        $this->line('-------------------------------------------');
    }

    public function migrate($tenancy)
    {
        $tenancy->configure()->use();

        $this->tenancy = $tenancy;

        DB::connection('tenancy')->getDatabaseName();

        $this->lineHeader("Migrating Tenancy #{$tenancy->id} ({$tenancy->name})");

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

        $tenancy->configureBack()->use();
    }
}
