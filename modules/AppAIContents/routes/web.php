<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAIContents\Http\Controllers\AppAIContentsController;

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
        Route::group(["prefix" => "ai-contents"], function () {
            Route::resource('/', AppAIContentsController::class)->names('app.ai-contents');
            Route::post('categories', [AppAIContentsController::class, 'categories'])->name('app.ai-contents.categories');
            Route::post('templates', [AppAIContentsController::class, 'templates'])->name('app.ai-contents.templates');
            Route::post('process', [AppAIContentsController::class, 'process'])->name('app.ai-contents.process');
            Route::post('process/{any}', [AppAIContentsController::class, 'process'])->name('app.ai-contents.process');
            Route::post('create-content', [AppAIContentsController::class, 'createContent'])->name('app.ai-contents.create_content');
            Route::post('popup-ai-content', [AppAIContentsController::class, 'popupAIContent'])->name('app.ai-contents.popupAIContent');

        });
// AI Content Strategy Routes
Route::middleware(['web', 'auth'])->prefix('ai-strategy')->name('ai-strategy.')->group(function () {
    Route::get('/', [AppAIContentsController::class, 'showContentStrategy'])->name('index');
    Route::post('/calendar/generate', [AppAIContentsController::class, 'generateContentCalendar'])->name('calendar.generate');
    Route::post('/topics/research', [AppAIContentsController::class, 'researchTopics'])->name('topics.research');
    Route::post('/competitor/analyze', [AppAIContentsController::class, 'analyzeCompetitor'])->name('competitor.analyze');
    Route::post('/hashtags/research', [AppAIContentsController::class, 'researchHashtags'])->name('hashtags.research');
    Route::post('/content/adapt', [AppAIContentsController::class, 'adaptContent'])->name('content.adapt');
    Route::get('/plans', [AppAIContentsController::class, 'getContentPlans'])->name('plans');
});
    });
});
