# Laravel multi-tenancy databases

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Travis](https://img.shields.io/travis/joaovdiasb/laravel-multi-tenancy.svg?style=flat-square)]()
[![Total Downloads](https://img.shields.io/packagist/dt/joaovdiasb/laravel-multi-tenancy.svg?style=flat-square)](https://packagist.org/packages/joaovdiasb/laravel-multi-tenancy)


## Install

```bash
composer require joaovdiasb/laravel-multi-tenancy
```


## Usage

Publish migration file and migrate
```bash
php artisan vendor:publish --provider="Joaovdiasb\LaravelMultiTenancy\LaravelMultiTenancyServiceProvider" --tag="migrations" && php artisan migrate --path=./database/migrations/tenant
```

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