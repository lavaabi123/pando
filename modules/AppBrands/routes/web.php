<?php

use Illuminate\Support\Facades\Route;
use Modules\AppBrands\Http\Controllers\AppBrandsController;

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
    Route::group(["prefix" => "brands"], function () {
        Route::resource('/', AppBrandsController::class)->names('app.brands');
        Route::post('update', [AppBrandsController::class, 'update'])->name('app.brands.update');
        Route::post('save', [AppBrandsController::class, 'save'])->name('app.brands.save');
        Route::post('list', [AppBrandsController::class, 'list'])->name('app.brands.list');
        Route::post('destroy', [AppBrandsController::class, 'destroy'])->name('app.brands.destroy');
    });
});