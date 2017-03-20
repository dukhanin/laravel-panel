<?php

use App\Http\Controllers\Sample\ProductsController;
use App\Http\Controllers\Sample\SectionsController;

Route::group([ 'prefix' => 'sample/products' ], function () {
    ProductsController::addRoutes([ 'name' => 'products' ]);
});

Route::group([ 'prefix' => 'sample/sections' ], function () {
    SectionsController::addRoutes([ 'name' => 'sections' ]);
});

Route::any('sample', function () {
    return redirect()->action('Sample\ProductsController@showList');
});
