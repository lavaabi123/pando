<?php

use Illuminate\Support\Facades\Route;
use Modules\AppPublishingApproval\Http\Controllers\AppPublishingApprovalController;

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
    Route::group(["prefix" => "publishing"], function () {
        Route::group(["prefix" => "approval"], function () {
            Route::resource('/', AppPublishingApprovalController::class)->names('app.publishing.approval');
            Route::post('list', [AppPublishingApprovalController::class, 'list'])->name('app.publishing.approval.list');
            Route::get('list', [AppPublishingApprovalController::class, 'list'])->name('app.publishing.approval.list');
            Route::get('create', [AppPublishingApprovalController::class, 'create'])->name('app.publishing.approval.create');
            Route::get('edit/{any}', [AppPublishingApprovalController::class, 'edit'])->name('app.publishing.approval.edit');
            Route::post('save', [AppPublishingApprovalController::class, 'save'])->name('app.publishing.approval.save');
            Route::post('destroy', [AppPublishingApprovalController::class, 'destroy'])->name('app.publishing.approval.destroy');
            Route::post('status/{any}', [AppPublishingApprovalController::class, 'status'])->name('app.publishing.approval.status');
            Route::post('pdf', [AppPublishingApprovalController::class, 'pdf'])->name('app.publishing.approval.pdf');
Route::get('test-pdf-html', function() {
    $ids = \DB::table('posts')->where('brand_id', session('brand_id'))->where('status', 2)->limit(3)->pluck('id')->toArray();
    
    $result = \Modules\AppPublishing\Models\Posts::whereIn('id', $ids)->get();
    $result = $result->map(function ($row) {
        $row->social_networks_count = \DB::table('posts')
            ->where('time_post', $row->time_post)
            ->where('grouping_data', $row->grouping_data)
            ->count();
        return $row;
    });
//echo "<pre>";print_r($result);exit;
    return view('apppublishingapproval::pdf', [
        'result'     => $result,
        'brand_name' => session('brand_name', 'TEST BRAND'),
    ]);
});			
        });
    });
});