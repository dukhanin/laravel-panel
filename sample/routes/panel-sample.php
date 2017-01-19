<?php

Route::any('panel-sample', function(){
    return redirect()->route('panel-sample.products');
});

Route::any('panel-sample/products/{path?}', 'PanelSampleController@products')->where('path', '^.*')->name('panel-sample.products');

Route::any('panel-sample/sections/{path?}', 'PanelSampleController@sections')->where('path', '^.*')->name('panel-sample.sections');
