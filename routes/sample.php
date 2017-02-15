<?php

use App\Http\Controllers\Sample\SampleProductsController;
use App\Http\Controllers\Sample\SampleSectionsController;

Route::group([ 'prefix' => 'sample/products' ], function () {
    SampleProductsController::addRoutes([ 'as' => 'products' ]);
});

Route::group([ 'prefix' => 'sample/sections' ], function () {
    SampleSectionsController::addRoutes([ 'as' => 'sections' ]);
});

Route::any('sample', function () {
    return redirect()->action('Sample\SampleProductsController@showList');
});
