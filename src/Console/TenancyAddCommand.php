<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Database;

class TenancyAddCommand extends BaseCommand
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
    public function handle(): int
    {
        $data = [
            'name'        => $this->argument('name') ?? $this->ask('What is the name of connection?'),
            'reference'   => $this->argument('reference') ?? $this->ask('What is the reference of connection?'),
            'db_host'     => $this->argument('db_host') ?? $this->ask('What is the host of connection?', '127.0.0.1'),
            'db_port'     => $this->argument('db_port') ?? $this->ask('What is the port of connection?', '3306'),
            'db_name'     => $this->argument('db_name') ?? $this->ask('What is the database name of connection?'),
            'db_user'     => $this->argument('db_user') ?? $this->ask('What is the username of connection?'),
            'db_password' => $this->argument('db_password') ?? $this->ask('What is the password of connection?')
        ];

        $validation = [
            'name'        => 'required|string|between:3,128',
            'reference'   => 'required|string|unique:tenancys|between:3,64',
            'db_host'     => 'nullable|string|between:1,128',
            'db_port'     => 'nullable|integer|between:1,10000',
            'db_name'     => 'required|string|unique:tenancys|between:3,128',
            'db_user'     => 'required|string|between:1,64',
            'db_password' => 'required|string'
        ];

        $validated = $this->validate($data, $validation);

        if (!$validated) return 1;

        $this->lineHeader('Adding Tenancy ' . $this->argument('name') ?: '');

        $tenancy = Tenancy::create($data);

        $this->info("Tenancy created » #{$tenancy->id} ({$tenancy->name})");

        try {
            Database::create()
                ->setDbName($tenancy->db_name)
                ->setDbUser($tenancy->db_user)
                ->setDbPassword($tenancy->db_password)
                ->setDbHost($tenancy->db_host)
                ->setDbPort($tenancy->db_port)
                ->createDatabase();
        } catch (\Exception $e) {
            if (isset($tenancy)) {
                $tenancy->delete();
                $this->info('There was a problem on create database, tenancy removed.');
            }
            
            $this->error($e->getMessage());
            
            return 1;
        }

        $this->info("Database created » {$tenancy->db_name}");

        $exitCode = $this->call('tenancy:migrate', [
            'tenancy' => $tenancy->id,
            '--fresh' => true,
            '--seed' => true
        ]);

        if ($exitCode === 1 && isset($tenancy)) {
            $tenancy->delete();
            $this->info('There was a problem on migrate, tenancy removed.');
        }

        return $exitCode;
    }
}
