<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use File;
use Storage;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Database;

class TenantBackupCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:backup {tenant?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenant backup';

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

    public function backup($tenant): void
    {
        $this->tenant = $tenant;
        
        $tenant->configure()->use();

        $this->lineHeader("Backup Tenant #{$tenant->id} ({$tenant->name})");

        if (!$this->confirm('Are you sure you want to continue?')) {
            throw new \Exception('Action canceled.');
        }

        $fileName = date('Y_m_d_His', time()) . (config('multitenancy.backup.compress') ? '.gz' : '.sql');
        $backupTempPath = config('multitenancy.backup.temp_folder');

        if (!File::exists($backupTempPath)) {
            File::makeDirectory($backupTempPath, 0775);
        }

        $conn = Database::create()
            ->setDbName($tenant->db_name)
            ->setDbUser($tenant->db_user)
            ->setDbPassword($tenant->db_password)
            ->setDbHost($tenant->db_host)
            ->setDbPort($tenant->db_port);

        if (config('multitenancy.backup.compress')) {
            $conn->setCompressor(true);
        }

        $conn->dumpToFile($backupTempFullPath = ($backupTempPath . $fileName));

        $this->info("Database dump finished » {$backupTempFullPath}");

        foreach (config('multitenancy.backup.disks') as $disk) {
            $backupPath = Storage::disk($disk)->getAdapter()->getPathPrefix();

            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0775);
            }

            File::put($backupFullPath = ($backupPath . $fileName), File::get($backupTempFullPath));
            File::delete($backupTempFullPath);

            $this->info("Copying to backup disk [{$disk}] » {$backupFullPath}");
        }

        $tenant->configureBack()->use();
    }
}
