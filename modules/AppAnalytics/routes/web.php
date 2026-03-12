<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAnalytics\Http\Controllers\AppAnalyticsController;
use Modules\AppAnalytics\Http\Controllers\AllAccountsAnalyticsController;

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

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "app"], function () {
        Route::group(["prefix" => "analytics"], function () {
            Route::resource('/', AppAnalyticsController::class)->names('app.analytics');
            Route::get('{social}/{id_secure}', [AppAnalyticsController::class, 'show'])->name('app.analytics.show');
            Route::post('{social}/{id_secure}/export-pdf', [AppAnalyticsController::class, 'exportPdf'])->name('analytics.export.pdf');
			// Consolidated view
            Route::get('consolidated', [AppAnalyticsController::class, 'consolidated'])->name('app.analytics.consolidated');
            
            // Export all - POST only for chart data
            Route::post('export-all-pdf', [AppAnalyticsController::class, 'exportAllPdf'])->name('app.analytics.export-all-pdf');
            Route::get('export-all-pdf', [AppAnalyticsController::class, 'exportAllPdf'])->name('app.analytics.export-all-pdf');

            Route::get('reportnew', [AppAnalyticsController::class,'reportnew'])->name('app.analytics.reportnew');
            Route::post('reportnew/pdf', [AppAnalyticsController::class,'generatePDF'])->name('app.analytics.reportnew.pdf');

             Route::post('exportnew-pdf', [AppAnalyticsController::class, 'exportnewPdf'])->name('analytics.exportnew.pdf');


            Route::get('preview', [AppAnalyticsController::class, 'preview'])->name('app.analytics.preview');

            // Stream PDF in browser tab
            Route::get('stream', [AppAnalyticsController::class, 'stream'])->name('app.analytics.stream');

            // Force-download the PDF
            Route::get('download', [AppAnalyticsController::class, 'download'])->name('app.analytics.download');

            // PDF download — POST (receives Chart.js base64 images from frontend)
            Route::post('pdf', [AppAnalyticsController::class, 'pdf'])->name('app.analytics.report.pdf');

        });
    });
});