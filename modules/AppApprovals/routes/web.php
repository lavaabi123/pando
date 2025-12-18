<?php

use Illuminate\Support\Facades\Route;
use Modules\AppApprovals\Http\Controllers\AppApprovalsController;

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
    Route::group(["prefix" => "approvals"], function () {
        Route::resource('/', AppApprovalsController::class)->names('app.approvals');
        Route::post('update', [AppApprovalsController::class, 'update'])->name('app.approvals.update');
        Route::post('save', [AppApprovalsController::class, 'save'])->name('app.approvals.save');
        Route::post('list', [AppApprovalsController::class, 'list'])->name('app.approvals.list');
        Route::post('list/popup', [AppApprovalsController::class, 'list'])->name('app.approvals.popup_list');
        Route::post('save_approval', [AppApprovalsController::class, 'saveApproval'])->name('app.approvals.save_approval');
        Route::post('get_approval', [AppApprovalsController::class, 'getApproval'])->name('app.approvals.get_approval');
        Route::post('destroy', [AppApprovalsController::class, 'destroy'])->name('app.approvals.destroy');
    });
});