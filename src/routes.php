<?php
Route::group(['middleware' => ['web']], function () {
    Route::group(['namespace' => 'Acr\Fat\Controllers', 'prefix' => 'acr/fat'], function () {
        Route::get('/kontrol', 'AcrFatController@kontrol');
        Route::group(['middleware' => ['auth']], function () {

        });
    });
});