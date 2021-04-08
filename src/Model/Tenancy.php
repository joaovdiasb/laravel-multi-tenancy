<?php

namespace Joaovdiasb\LaravelMultiTenancy\Model;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyException;

class Tenancy extends Model
{
    protected $table = 'tenancys';

    protected $connection = 'tenancy';

    protected array $originalConnection = [];

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
     * getOriginalConnection
     *
     * @return array
     */
    public function getOriginalConnection(): array
    {
        return $this->originalConnection;
    }

    /**
     * findFirstByKey
     *
     * @param string $key
     * @param string $value
     * 
     * @throws TenancyException
     * 
     * @return Tenancy
     */
    public static function findFirstByKey(string $key, string $value)
    {
        $tenancy = self::where($key, $value)->first();

        if (!$tenancy) {
            throw TenancyException::notFound($value);
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
        $this->originalConnection = config('database.connections.tenancy') + ['reference' => $this->reference];

        config([
            'database.connections.tenancy.host'     => $this->db_host,
            'database.connections.tenancy.port'     => $this->db_port,
            'database.connections.tenancy.database' => $this->db_name,
            'database.connections.tenancy.user'     => $this->db_user,
            'database.connections.tenancy.password' => $this->db_password
        ]);

        $this->configureTenancyFolder($this->reference);

        DB::purge('tenancy');

        return $this;
    }

    /**
     * configureBack
     *
     * @return Tenancy
     */
    public function configureBack(): Tenancy
    {
        config([
            'database.connections.tenancy.host'     => $this->originalConnection['host'],
            'database.connections.tenancy.port'     => $this->originalConnection['port'],
            'database.connections.tenancy.database' => $this->originalConnection['database'],
            'database.connections.tenancy.user'     => $this->originalConnection['username'],
            'database.connections.tenancy.password' => $this->originalConnection['password']
        ]);

        $this->configureTenancyFolder($this->originalConnection['reference']);

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
