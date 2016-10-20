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

Copy the all required package files to your application with the publish command:

```shell
php artisan vendor:publish --provider="Dukhanin\Panel\Providers\PanelServiceProvider"
```

This includes sample files

## Sample

To run sample just require routes/panel-sample.php from your routes/web.php

```php
require 'panel-sample.php';
```
