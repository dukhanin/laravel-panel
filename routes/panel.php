<?php

Route::group([ 'prefix' => 'panel/upload', 'namespace' => '\\Dukhanin\\Panel\\Http\\Controllers' ], function () {
    Route::post('', 'PanelFormUploadController@upload')->name('panel.upload.form');
    Route::post('createResize/{id}', 'PanelFormUploadController@createResize');
    Route::post('cropFromParent/{id}', 'PanelFormUploadController@cropFromParent');
    Route::post('delete/{id}', 'PanelFormUploadController@delete');

    Route::post('tinymce', 'PanelTinymceUploadController@upload')->name('panel.upload.tinymce');
});