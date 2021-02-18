<?php

namespace Joaovdiasb\LaravelMultiTenancy\Model;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyFail;

class Tenancy extends Model
{
    use HasFactory;

    protected $table = 'tenancys';

    protected $connection = 'tenancy';

    protected $fillable = [
        'name',
        'reference',
        'db_host',
        'db_port',
        'db_name',
        'db_user',
        'db_password'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(fn ($model) => $model->uuid = Str::uuid());
    }

    /**
     * findFirstByKey
     *
     * @param string $key
     * @param string $value
     * 
     * @throws TenancyFail
     * 
     * @return Tenancy
     */
    public static function findFirstByKey(string $key, string $value)
    {
        $tenancy = self::where($key, $value)->first();

        if (!$tenancy) {
            throw TenancyFail::notFound($value);
        }

        return $tenancy;
    }

    /**
     * getDbPasswordAttribute
     *
     * @param string $value
     * 
     * @return string
     */
    public function getDbPasswordAttribute(string $value): string
    {
        $encrypter = new Encrypter(config('tenancy.encrypt_key'), 'AES-256-CBC');

        return $encrypter->decryptString($value);
    }

    /**
     * setDbPasswordAttribute
     *
     * @param string $value
     * 
     * @return void
     */
    public function setDbPasswordAttribute(string $value): void
    {
        $encrypter = new Encrypter(config('tenancy.encrypt_key'), 'AES-256-CBC');

        $this->attributes['db_password'] = $encrypter->encryptString($value);
    }

    /**
     * configureTenancyFolder
     *
     * @param string $reference
     * 
     * @return void
     */
    private function configureTenancyFolder(string $reference): void
    {
        foreach (array_keys(config('filesystems.disks')) as $disk) {
            config([
                'filesystems.disks.' . $disk . '.root' => config('filesystems.disks.' . $disk . '.root') . $reference
            ]);
        };
    }

    /**
     * configure
     *
     * @return Tenancy
     */
    public function configure(): Tenancy
    {
        config([
            'database.connections.tenancy.host' => $this->db_host,
            'database.connections.tenancy.port' => $this->db_port,
            'database.connections.tenancy.database' => $this->db_name,
            'database.connections.tenancy.user' => $this->db_user,
            'database.connections.tenancy.password' => $this->db_password
        ]);

        $this->configureTenancyFolder($this->reference);

        DB::purge('tenancy');

        return $this;
    }

    /**
     * configureManual
     *
     * @param string $dbHost
     * @param string $dbPort
     * @param string $dbDatabase
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $reference
     * 
     * @return Tenancy
     */
    public function configureManual(string $dbHost = null, string $dbPort = null, string $dbDatabase = null, string $dbUser = null, string $dbPassword = null, string $reference = null): Tenancy
    {
        config([
            'database.connections.tenant.host' => $dbHost ?: $this->db_host,
            'database.connections.tenant.port' => $dbPort ?: $this->db_port,
            'database.connections.tenant.database' => $dbDatabase ?: $this->db_name,
            'database.connections.tenant.user' => $dbUser ?: $this->db_user,
            'database.connections.tenant.password' => $dbPassword ?: $this->db_password
        ]);

        $this->configureTenancyFolder($reference ?: $this->reference);

        DB::purge('tenancy');

        return $this;
    }

    /**
     * use
     *
     * @return Tenancy
     */
    public function use(): Tenancy
    {
        app()->forgetInstance('tenancy');
        
        app()->instance('tenancy', $this);

        return $this;
    }
}
