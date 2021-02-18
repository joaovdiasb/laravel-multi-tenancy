<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use File;
use Storage;
use Illuminate\Console\Command;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;

class TenancyBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:backup {tenancy?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenancy backup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->argument('tenancy')) {
            $this->backup(
                Tenancy::find($this->argument('tenancy'))
            );
        } else {
            Tenancy::all()->each(
                fn ($tenancy) => $this->backup($tenancy)
            );
        }
    }

    public function backup($tenancy)
    {
        $tenancy->configure()->use();

        $this->line('');
        $this->line('-------------------------------------------');
        $this->line("Backup Tenancy #{$tenancy->id} ({$tenancy->name})");
        $this->line('-------------------------------------------');

        if (!$this->confirm('Are you sure you want to continue?')) {
            return $this->line('Action canceled.');
        }

        $fileName = date('Y_m_d_His', time()) . (config('tenancy.backup.compress') ? '.gz' : '.sql');
        $backupTempPath = config('tenancy.backup.temp_folder');

        if (!File::exists($backupTempPath)) {
            File::makeDirectory($backupTempPath, 0775);
        }

        $databaseTypes = [
            'mysql' => 'MySql'
        ];

        $databaseClass = '\Joaovdiasb\LaravelMultiTenancy\Utils\Database\\' . $databaseTypes[strtolower(config('tenancy.backup.database'))];

        $conn = str_replace("'", '', $databaseClass)::create()
            ->setDbName($tenancy->db_name)
            ->setDbUser($tenancy->db_user)
            ->setDbPassword($tenancy->db_password);

        if (config('tenancy.backup.compress')) {
            $conn->setCompressor(true);
        }

        $conn->dumpToFile($backupTempFullPath = ($backupTempPath . $fileName));

        $this->info("Database dump finished » {$backupTempFullPath}");

        foreach (config('tenancy.backup.disks') as $disk) {
            $backupPath = Storage::disk($disk)->getAdapter()->getPathPrefix();

            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0775);
            }

            File::put($backupFullPath = ($backupPath . $fileName), File::get($backupTempFullPath));
            File::delete($backupTempFullPath);

            $this->info("Copying to backup disk [{$disk}] » {$backupFullPath}");
        }
    }
}
