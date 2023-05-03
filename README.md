Laravel OPENIMIS INSURANCE
========

## Installation

Run `composer require insurance/openimis`.

In your `config/app.php` file, add the Laravel API Key service provider to the end of the `providers` array.

```php
Insurance\Openimis\OpenImisServiceProvider::class,
```

Publish the migration and config fiels

    $ php artisan openimis:install

Run the migrations

    $ php artisan migrate --path=database/migrations/openimis

Developed by: Roshan Shrestha
Senior Team Lead (Software Enigineer)
