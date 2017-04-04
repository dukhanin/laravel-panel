<?php

use App\Http\Controllers\Sample\ProductsController;
use App\Http\Controllers\Sample\SectionsController;

ProductsController::routes([
    'prefix' => 'sample/products',
    'as' => 'products',
]);

SectionsController::routes([
    'prefix' => 'sample/sections',
    'as' => 'sections.',
]);

Route::any('sample', function () {
    return redirect()->action('Sample\ProductsController@showList');
});
