<?php

Route::group([ 'prefix' => 'panel/upload', 'namespace' => 'Panel' ], function () {
    Route::any('', 'PanelUploadController@upload');
    Route::post('createResize/{id}', 'PanelUploadController@createResize');
    Route::any('cropFromParent/{id}', 'PanelUploadController@cropFromParent'); // @todo only post!
    Route::any('delete/{id}', 'PanelUploadController@delete'); // @todo only post!
});
