<?php

use Illuminate\Support\Facades\Route;
use Modules\AppHandles\Http\Controllers\AppHandlesController;

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
    Route::group(["prefix" => "handles"], function () {
        Route::resource('/', AppHandlesController::class)->names('app.handles');
        Route::post('update', [AppHandlesController::class, 'update'])->name('app.handles.update');
        Route::post('save', [AppHandlesController::class, 'save'])->name('app.handles.save');
        Route::post('list', [AppHandlesController::class, 'list'])->name('app.handles.list');
        Route::post('list/popup', [AppHandlesController::class, 'list'])->name('app.handles.popup_list');
        Route::post('save_handle', [AppHandlesController::class, 'saveHandle'])->name('app.handles.save_handle');
        Route::post('get_handle', [AppHandlesController::class, 'getHandle'])->name('app.handles.get_handle');
        Route::post('destroy', [AppHandlesController::class, 'destroy'])->name('app.handles.destroy');
    });
});