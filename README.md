# laravel-panel
Laravel Panel constructor


## Installation
Require this package with composer:

```shell
composer require dukhanin/laravel-panel
```

After updating composer, add _dukhanin/laravel-panel_ and _intervention/image_ packages supplying your application config  with following rows

_config/app.php_

```php
'providers' => [
  ...
  /*
   * Package Service Providers...
   */
  Intervention\Image\ImageServiceProvider::class,
  Dukhanin\Panel\Providers\PanelServiceProvider::class,
],

'aliases' => [
  ...
  'Image' => Intervention\Image\Facades\Image::class
]
```

Copy required package files to your application with the publish command:

```shell
php artisan vendor:publish --provider="Dukhanin\Panel\Providers\PanelServiceProvider"
```

Update composer autoload cache:

```shell
composer dump-autoload
```

Require panel routes to your web routes file

_routes/web.php_

```php
require 'panel.php';
```
