<?php

use App\Http\Controllers\Sample\SampleProductsController;
use App\Http\Controllers\Sample\SampleSectionsController;

Route::group([ 'prefix' => 'sample/products' ], function () {
    SampleProductsController::routes([ 'as' => 'products' ]);
});

Route::group([ 'prefix' => 'sample/sections' ], function () {
    SampleSectionsController::routes([ 'as' => 'sections' ]);
});

Route::any('panel-sample', function () {
    return redirect()->action('Sample\SampleProductsController@showList');
});
