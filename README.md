# Laravel multi-tenancy databases

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Travis](https://img.shields.io/travis/joaovdiasb/laravel-multi-tenancy.svg?style=flat-square)]()
[![Total Downloads](https://img.shields.io/packagist/dt/joaovdiasb/laravel-multi-tenancy.svg?style=flat-square)](https://packagist.org/packages/joaovdiasb/laravel-multi-tenancy)


## Installation

Install via composer
```bash
composer require joaovdiasb/laravel-multi-tenancy
```

## Configuration

- Change .env with the tenancy database connection and add TENANCY_ENCRYPT_KEY with 32 random characters string.

- Publish provider and migrate:
```bash
php artisan vendor:publish --provider="Joaovdiasb\LaravelMultiTenancy\LaravelMultiTenancyServiceProvider" && php artisan migrate --path=./database/migrations/tenancy
```

## Usage
1. Add middleware *tenancy* on the routes that you need;
2. Send *X-Ref* header on request with tenancy reference, defined on tenancys table.

## Commands
- Add tenancy:
```bash
php artisan tenancy:add {name?} {reference?} {db_name?} {db_user?} {db_password?} {db_host?} {db_port?}
```
> **{reference?}** Used to pass on request header to identify tenancy

> All params are optional, if not present, will be asked on console

- Migrate tenancy:
```bash
php artisan tenancy:migrate {tenancy?} {--fresh} {--seed}
```
> **{tenancy?}** Select tenancy by id, if not present, all tenancys are selected

> **{--fresh}** Is present, will drop all tables from the database

> **{--seed}** Is present, call seeds

- Seed tenancy:
```bash
php artisan tenancy:seed {tenancy?} {--class=*}
```
> **{tenancy?}** Select tenancy by id, if is not present, all tenancys are selected

> **{--class=*}** Is required, specify class name

- Backup tenancy:
```bash
php artisan tenancy:backup {tenancy?}
```
> **{tenancy?}** Select tenancy by id, if is not present, all tenancys are selected

## Testing
Run the tests with:

```bash
vendor/bin/phpunit
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Security

If you discover any security-related issues, please email j.v_dias@hotmail.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](/LICENSE.md) for more information.