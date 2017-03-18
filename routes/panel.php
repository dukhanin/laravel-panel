<?php

Route::group([ 'prefix' => 'panel/upload' ], function () {
    Route::post('', '\\Dukhanin\\Panel\\Http\\ControllersPanelFormUploadController@upload')->name('panel.upload.form');
    Route::post('createResize/{id}', '\\Dukhanin\\Panel\\Http\\Controllers\\PanelFormUploadController@createResize');
    Route::post('cropFromParent/{id}', '\\Dukhanin\\Panel\\Http\\Controllers\\PanelFormUploadController@cropFromParent');
    Route::post('delete/{id}', '\\Dukhanin\\Panel\\Http\\Controllers\\PanelFormUploadController@delete');

    Route::post('tinymce', '\\Dukhanin\\Panel\\Http\\Controllers\\PanelTinymceUploadController@upload')->name('panel.upload.tinymce');
});