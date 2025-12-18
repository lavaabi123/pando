<?php

use Illuminate\Support\Facades\Route;
use Modules\AppHashtags\Http\Controllers\AppHashtagsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(["prefix" => "app"], function () {
    Route::group(["prefix" => "hashtags"], function () {
        Route::resource('/', AppHashtagsController::class)->names('app.hashtags');
        Route::post('update', [AppHashtagsController::class, 'update'])->name('app.hashtags.update');
        Route::post('save', [AppHashtagsController::class, 'save'])->name('app.hashtags.save');
        Route::post('list', [AppHashtagsController::class, 'list'])->name('app.hashtags.list');
        Route::post('list/popup', [AppHashtagsController::class, 'list'])->name('app.hashtags.popup_list');
        Route::post('save_hashtag', [AppHashtagsController::class, 'saveHashtag'])->name('app.hashtags.save_hashtag');
        Route::post('get_hashtag', [AppHashtagsController::class, 'getHashtag'])->name('app.hashtags.get_hashtag');
        Route::post('destroy', [AppHashtagsController::class, 'destroy'])->name('app.hashtags.destroy');
    });
});