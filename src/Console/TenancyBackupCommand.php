<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use File;
use Storage;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Database;

class TenancyBackupCommand extends BaseCommand
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

    public function backup($tenancy): void
    {
        $this->tenancy = $tenancy;
        
        $tenancy->configure()->use();

        $this->lineHeader("Backup Tenancy #{$tenancy->id} ({$tenancy->name})");

        if (!$this->confirm('Are you sure you want to continue?')) {
            throw new \Exception('Action canceled.');
        }

        $fileName = date('Y_m_d_His', time()) . (config('tenancy.backup.compress') ? '.gz' : '.sql');
        $backupTempPath = config('tenancy.backup.temp_folder');

        if (!File::exists($backupTempPath)) {
            File::makeDirectory($backupTempPath, 0775);
        }

        $conn = Database::create()
            ->setDbName($tenancy->db_name)
            ->setDbUser($tenancy->db_user)
            ->setDbPassword($tenancy->db_password)
            ->setDbHost($tenancy->db_host)
            ->setDbPort($tenancy->db_port);

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

        $tenancy->configureBack()->use();
    }
}
