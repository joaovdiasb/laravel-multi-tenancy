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
        $this->line('Adicionando Tenancy ' . $this->argument('name') ?: '');
        $this->line('-------------------------------------------');

        $tenancy = Tenancy::create([
            'name'        => $this->argument('name') ?? $this->ask('Qual o nome do tenancy?'),
            'reference'   => $this->argument('reference') ?? $this->ask('Qual a nome da referência do tenancy?'),
            'db_host'     => $this->argument('db_host') ?? $this->ask('Qual o host do banco?', '127.0.0.1'),
            'db_port'     => $this->argument('db_port') ?? $this->ask('Qual a porta do banco?', '3306'),
            'db_name' => $this->argument('db_name') ?? $this->ask('Qual o nome do banco?'),
            'db_user'     => $this->argument('db_user') ?? $this->ask('Qual o nome do usuário do banco?'),
            'db_password' => $this->argument('db_password') ?? $this->ask('Qual a senha do banco?')
        ]);

        $this->info("Tenancy criado » #{$tenancy->id} ({$tenancy->name})");

        $oldConfig = config('database.connections.tenancy');

        try {
            // DB::connection('tenancy')->statement("CREATE DATABASE {$tenancy->db_name} CHARACTER SET utf8 COLLATE utf8_unicode_ci");
            exec('sudo mysql --host=' . escapeshellarg($tenancy->db_host) . ' -P ' . escapeshellarg($tenancy->db_port) .
                ' -u ' . escapeshellarg($tenancy->db_user) .  " --password='" . escapeshellarg($tenancy->db_password) .
                "' -e 'CREATE DATABASE " . escapeshellarg($tenancy->db_name) . " CHARACTER SET utf8 COLLATE utf8_unicode_ci'", $output);

            $this->info(json_encode($output));
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
