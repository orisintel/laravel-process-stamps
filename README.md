# Laravel Process Stamps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orisintel/laravel-process-stamps.svg?style=flat-square)](https://packagist.org/packages/orisintel/laravel-process-stamps)
[![Build Status](https://img.shields.io/travis/orisintel/laravel-process-stamps/master.svg?style=flat-square)](https://travis-ci.org/orisintel/laravel-process-stamps)
[![Total Downloads](https://img.shields.io/packagist/dt/orisintel/laravel-process-stamps.svg?style=flat-square)](https://packagist.org/packages/orisintel/laravel-process-stamps)

It is sometimes very useful to know which process created or modified a particular record in your database. This package provides a trait to add to your Laravel models which automatically logs that for you.

## Installation

You can install the package via composer:

```bash
composer require orisintel/laravel-process-stamps
```

## Configuration

``` php
php artisan vendor:publish --provider="\OrisIntel\ProcessStamps\ProcessStampsServiceProvider"
```

Running the above command will publish both the migration and the config file.

## Usage

After adding the proper fields to your table, add the trait to your model.

``` php
// User model
class User extends Model
{
    use ProcessStampable;

```

### Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email [tschlick@orisintel.com](mailto:tschlick@orisintel.com) instead of using the issue tracker.

## Credits

- [Tom Schlick](https://github.com/tomschlick)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
