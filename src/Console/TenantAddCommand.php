<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Database;

class TenantAddCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:add {name?} {reference?} {db_name?} {db_user?} {db_password?} {db_host?} {db_port?} {driver?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenant add';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $data = [
            'name'        => $this->argument('name') ?? $this->ask('What is the name of connection?'),
            'reference'   => $this->argument('reference') ?? $this->ask('What is the reference of connection?'),
            'db_host'     => $this->argument('db_host') ?? $this->ask('What is the host of connection?', '127.0.0.1'),
            'db_port'     => $this->argument('db_port') ?? $this->ask('What is the port of connection?', '3306'),
            'db_name'     => $this->argument('db_name') ?? $this->ask('What is the database name of connection?'),
            'db_user'     => $this->argument('db_user') ?? $this->ask('What is the username of connection?'),
            'db_password' => $this->argument('db_password') ?? $this->ask('What is the password of connection?'),
            'driver'      => $this->argument('driver') ?? $this->ask('What is the driver of connection?')
        ];

        $validation = [
            'name'        => 'required|string|between:3,128',
            'reference'   => 'required|string|unique:tenants|between:3,64',
            'db_host'     => 'nullable|string|between:1,128',
            'db_port'     => 'nullable|integer|between:1,10000',
            'db_name'     => 'required|string|unique:tenants|between:3,128',
            'db_user'     => 'required|string|between:1,64',
            'db_password' => 'required|string',
            'driver'      => 'nullable|string'
        ];

        $validated = $this->validate($data, $validation);

        if (!$validated) return 1;

        $this->lineHeader('Adding Tenant ' . $this->argument('name') ?: '');

        $tenant = Tenant::create($data);

        $this->info("Tenant created » #{$tenant->id} ({$tenant->name})");

        try {
            Database::create($data['driver'])
                ->setDbName($tenant->db_name)
                ->setDbUser($tenant->db_user)
                ->setDbPassword($tenant->db_password)
                ->setDbHost($tenant->db_host)
                ->setDbPort($tenant->db_port)
                ->createDatabase();
        } catch (\Exception $e) {
            if (isset($tenant)) {
                $tenant->delete();
                $this->info('There was a problem on create database, tenant removed.');
            }
            
            $this->error($e->getMessage());
            
            return 1;
        }

        $this->info("Database created » {$tenant->db_name}");

        $exitCode = $this->call('tenant:migrate', [
            'tenant' => $tenant->id,
            '--fresh' => true,
            '--seed' => true
        ]);

        if ($exitCode === 1 && isset($tenant)) {
            $tenant->delete();
            $this->info('There was a problem on migrate, tenant removed.');
        }

        return $exitCode;
    }
}
