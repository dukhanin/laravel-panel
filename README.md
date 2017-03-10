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
  Dukhanin\Panel\Providers\PanelServiceProvider::class
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

## Running sample

Publish sample files (they wouldnt publish with vendor:publish command without defined --tag=sample)

```shell
php artisan vendor:publish --provider="Dukhanin\Panel\Providers\PanelServiceProvider" --tag=sample
```


Update composer autoload cache:

```shell
composer dump-autoload
```


Run sample migrations and seeders

```shell
php artisan migrate
php artisan db:seed --class=SampleSeeder
```

Require routes for panel package and samples routes

_routes/web.php_

```php
require 'panel.php';
require 'sample.php';
```

Go and check out */sample* url in your app for sample panels

Sample classes are located in your app directory:

```shell
app/Http/Controllers/
app/Sample/
```
