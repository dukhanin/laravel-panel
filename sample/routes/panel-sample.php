<?php

Route::any('panel-sample', function(){
    return redirect()->action('PanelSampleProductsController@showList');
});

Route::group(['prefix' => 'panel-sample/products'], function(){
    \App\Http\Controllers\PanelSampleProductsController::routes();
});

Route::group(['prefix' => 'panel-sample/sections'], function(){
    \App\Http\Controllers\PanelSampleSectionsController::routes();
});

