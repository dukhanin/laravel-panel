<?php
namespace Dukhanin\Panel\Controllers\Concerns;

use Illuminate\Support\Facades\Route;

trait SetsRoutes
{
    static public function routes(array $attributes = null)
    {
        $attributes = $attributes ?: ['middleware' => ['web']];

        if (empty($attributes['as'])) {
            $attributes['as'] = class_basename(static::class);
        }

        if (empty($attributes['prefix'])) {
            $attributes = array_set($attributes, 'prefix',
                trim(rtrim(kebab_case(class_basename(static::class)), 'controller'), '-'));
        }

        $attributes['as'] = rtrim($attributes['as'], '.').'.';

        Route::group($attributes, function ($router) {
            static::initRoutes();
            static::initFeaturesRoutes();
        });
    }

    static protected function routeAction($method)
    {
        return '\\'.static::class.'@'.$method;
    }

    static protected function initRoutes()
    {
        Route::get('', static::routeAction('showList'))->name('showList');
    }

    static protected function initFeaturesRoutes()
    {
        foreach (class_uses_recursive($class = get_called_class()) as $trait) {
            if (is_callable([$class, $method = 'routesFor'.class_basename($trait)])) {
                $class::$method();
            }
        }
    }
}