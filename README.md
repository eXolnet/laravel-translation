# Laravel Translation

[![Latest Stable Version](https://poser.pugx.org/eXolnet/laravel-translation/v/stable?format=flat-square)](https://packagist.org/packages/eXolnet/laravel-translation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/github/workflow/status/eXolnet/laravel-translation/tests?label=tests&style=flat-square)](https://github.com/eXolnet/laravel-translation/actions?query=workflow%3Atests)
[![Total Downloads](https://img.shields.io/packagist/dt/eXolnet/laravel-translation.svg?style=flat-square)](https://packagist.org/packages/eXolnet/laravel-translation)

Library to manage Laravel translations

## Installation

Require this package with composer:

```
composer require exolnet/laravel-translation
```

In Laravel 5.5, the service provider and facade will automatically get registered. For older versions of the framework, follow the steps below:

Register the service provider in `config/app.php`

```php
'providers' => [
    // [...]
    Exolnet\Translation\TranslationServiceProvider::class,
],
```

To make sure the routing system is using the one supporting the translation you must edit your `app/Http/Kernel.php` to add the usage of the custom user dispatcher

    use \Exolnet\Translation\Traits\CustomRouterDispatcher;

Now you're ready to start using the translation in your application.

## Config

### Config Files

In order to edit the default configuration (where for e.g. you can find `available_locales`) for this package you may execute:

```
php artisan vendor:publish --provider="Exolnet\Translation\TranslationServiceProvider"
```

After that, `config/translation.php` will be created. Inside this file you will find all the fields that can be edited in this package.

## Usage

Exolnet Translation uses the URL given for the request. In order to achieve this purpose, a route group should be added into the `routes/web.php` file. It will filter all pages that must be localized.

```php
// routes/web.php

Route::groupLocales(function () {
    Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);
});

```

Once this route group is added to the routes file, a user can access all locales added into `available_locales`. For example, a user can now access two different locales, using the following addresses:

```
http://url-to-laravel/en
http://url-to-laravel/fr
```

If you when to remove the locale prefix on the base locale you need to set the `$avoidPrefixOnBaseLocale` to `true` when defining the groupLocale

```php
// routes/web.php

Route::groupLocales(function () {
    Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);
})->hiddenBaseLocale();

```

## Testing

To run the phpUnit tests, please use:

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE OF CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@exolnet.com instead of using the issue tracker.

## Credits

- [Alexandre D'Eschambeault](https://github.com/xel1045)
- [Simon Gaudreau](https://github.com/Gandhi11)
- [Patrick Gagnon-Renaud](https://github.com/pgrenaud)
- [Tom Rochette](https://github.com/tomzx)
- [All Contributors](../../contributors)

## License

This code is licensed under the [MIT license](http://choosealicense.com/licenses/mit/).
Please see the [license file](LICENSE) for more information.
