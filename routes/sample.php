<?php

use App\Http\Controllers\Sample\ProductsController;
use App\Http\Controllers\Sample\SectionsController;

Route::group([ 'prefix' => 'sample/products' ], function () {
    ProductsController::addRoutes([ 'as' => 'products' ]);
});

Route::group([ 'prefix' => 'sample/sections' ], function () {
    SectionsController::addRoutes([ 'as' => 'sections' ]);
});

Route::any('sample', function () {
    return redirect()->action('Sample\ProductsController@showList');
});
