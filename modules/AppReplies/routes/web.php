<?php

use Illuminate\Support\Facades\Route;
use Modules\AppReplies\Http\Controllers\AppRepliesController;

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
    Route::group(["prefix" => "replies"], function () {
        Route::resource('/', AppRepliesController::class)->names('app.replies');
        Route::post('update', [AppRepliesController::class, 'update'])->name('app.replies.update');
        Route::post('save', [AppRepliesController::class, 'save'])->name('app.replies.save');
        Route::post('list', [AppRepliesController::class, 'list'])->name('app.replies.list');
        Route::post('list/popup', [AppRepliesController::class, 'list'])->name('app.replies.popup_list');
        Route::post('save_reply', [AppRepliesController::class, 'saveReply'])->name('app.replies.save_reply');
        Route::post('get_reply', [AppRepliesController::class, 'getReply'])->name('app.replies.get_reply');
        Route::post('destroy', [AppRepliesController::class, 'destroy'])->name('app.replies.destroy');
    });
});