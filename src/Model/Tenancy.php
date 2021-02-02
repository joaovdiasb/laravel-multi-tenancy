<?php

namespace Joaovdiasb\LaravelMultiTenancy\Model;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyNotFoundException;

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
        'db_database',
        'db_user',
        'db_password'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(fn ($model) => $model->uuid = Str::uuid());
    }

    public static function findFirstByKey(string $key, string $value)
    {
        $tenancy = self::where($key, $value)->first();

        if (!$tenancy) {
            throw new TenancyNotFoundException();
        }

        return $tenancy;
    }

    public function getDbPasswordAttribute($value): string
    {
        $encrypter = new Encrypter(config('app.encrypt_key'), 'AES-256-CBC');

        return $encrypter->decryptString($value);
    }

    public function setDbPasswordAttribute($value): void
    {
        $encrypter = new Encrypter(config('app.encrypt_key'), 'AES-256-CBC');

        $this->attributes['db_password'] = $encrypter->encryptString($value);
    }

    public function configure(): Tenancy
    {
        config([
            'database.connections.tenancy.host' => $this->db_host,
            'database.connections.tenancy.port' => $this->db_port,
            'database.connections.tenancy.database' => $this->db_database,
            'database.connections.tenancy.user' => $this->db_user,
            'database.connections.tenancy.password' => $this->db_password,
            'filesystems.disks.local.root' => $this->reference
        ]);

        DB::purge('tenancy');

        return $this;
    }

    public function use(): Tenancy
    {
        app()->forgetInstance('tenancy');
        
        app()->instance('tenancy', $this);

        return $this;
    }
}
