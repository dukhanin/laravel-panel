# laravel-panel
Laravel Panel constructor

(under construction)

Release in Jan 2017

## Installation

Require this package with composer:

```shell
composer require dukhanin/laravel-panel
```

After updating composer, add the PanelServiceProvider to the providers array in config/app.php

### Laravel 5.x:

```php
Dukhanin\Panel\Providers\PanelServiceProvider::class,
```

Copy all required package files to your application with the publish command:

```shell
php artisan vendor:publish --provider="Dukhanin\Panel\Providers\PanelServiceProvider"
```

Update composer autoload cache:

```shell
composer dump-autoload
```

This includes sample files

## Running sample

Run sample migrations and seeders

```shell
php artisan migrate
php artisan db:seed --class=PanelSampleSeeder
```

Require routes/panel-sample.php from your routes/web.php

```php
require 'panel-sample.php';
```

Go and check out */panel-sample/products* url in your app for sample panels

Sample classes are located in your app directory:

```shell
app/Http/Controllers/PanelSampleController.php
app/Panel/Sample/
```
