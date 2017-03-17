<?php

Route::group([ 'prefix' => 'panel/upload', 'namespace' => 'Panel' ], function () {
    Route::post('', 'PanelUploadController@upload');
    Route::post('createResize/{id}', 'PanelUploadController@createResize');
    Route::post('cropFromParent/{id}', 'PanelUploadController@cropFromParent');
    Route::post('delete/{id}', 'PanelUploadController@delete');

    Route::post('tinymce', 'PanelTinymceUploadController@upload');
});