<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;

class TenancyAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:add {name?} {reference?} {db_name?} {db_user?} {db_password?} {db_host?} {db_port?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenancy add';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('');
        $this->line('-------------------------------------------');
        $this->line('Adding Tenancy ' . $this->argument('name') ?: '');
        $this->line('-------------------------------------------');

        $tenancy = Tenancy::create([
            'name'        => $this->argument('name') ?? $this->ask('What is the name of tenancy?'),
            'reference'   => $this->argument('reference') ?? $this->ask('What is the reference of tenancy?'),
            'db_host'     => $this->argument('db_host') ?? $this->ask('What is the host of database?', '127.0.0.1'),
            'db_port'     => $this->argument('db_port') ?? $this->ask('What is the port of database?', '3306'),
            'db_name'     => $this->argument('db_name') ?? $this->ask('What is the name of database?'),
            'db_user'     => $this->argument('db_user') ?? $this->ask('What is the username of database?'),
            'db_password' => $this->argument('db_password') ?? $this->ask('What is the password of database?')
        ]);

        $this->info("Tenancy created » #{$tenancy->id} ({$tenancy->name})");

        $oldConfig = config('database.connections.tenancy');

        try {
            $databaseTypes = [
                'mysql' => 'MySql'
            ];
    
            $databaseClass = '\Joaovdiasb\LaravelMultiTenancy\Utils\Database\\' . $databaseTypes[strtolower(config('tenancy.backup.database'))];

            str_replace("'", '', $databaseClass)::create()
                ->setDbName($tenancy->db_name)
                ->setDbUser($tenancy->db_user)
                ->setDbPassword($tenancy->db_password)
                ->createDatabase();

            $this->info("Database criada » {$tenancy->db_name}");

            $tenancy->configure()->use();

            $this->line('');
            $this->line('-------------------------------------------');
            $this->line("Migrating Tenancy #{$tenancy->id} ({$tenancy->name})");
            $this->line('-------------------------------------------');

            DB::connection('tenancy')->getDatabaseName();

            $this->call('migrate:fresh', [
                '--force' => true,
                '--seed' => true
            ]);

            if (config('tenancy.passport')) {
                $this->call('passport:client', [
                    '--personal' => true,
                    '--no-interaction' => true
                ]); 
            }
        } catch (\Exception $e) {
            $tenancy->configureManual(
                $oldConfig['host'],
                $oldConfig['port'],
                $oldConfig['database'],
                $oldConfig['username'],
                $oldConfig['password']
            )->use();

            $tenancy->delete();

            $this->info('Houve um problema, tenancy removido.');

            return $this->info($e->getMessage());
        }
    }
}
