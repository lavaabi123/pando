<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\AppPublishing\Http\Controllers\AppPublishingController;

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
        Route::group(["prefix" => "publishing"], function () {
            Route::resource('/', AppPublishingController::class)->only(['index'])->names('app.publishing');
            Route::resource('/calendar', AppPublishingController::class)->only(['index'])->names('app.publishing.calendar');
            Route::get('events', [AppPublishingController::class, 'events'])->name('app.publishing.events');
			Route::post('alllist/{type}/{category}/{date}', [AppPublishingController::class, 'alllist']);
            Route::get('events_count', [AppPublishingController::class, 'events_count'])->name('app.publishing.events_count');
            Route::post('composer', [AppPublishingController::class, 'composer'])->name('app.publishing.composer');
            Route::get('composer', [AppPublishingController::class, 'composerget'])->name('app.publishing.composer');
            Route::post('preview', [AppPublishingController::class, 'preview'])->name('app.publishing.preview');
            Route::post('comments', [AppPublishingController::class, 'comments'])->name('app.publishing.comments');
            Route::post('comments/store', [AppPublishingController::class, 'store'])->name('app.publishing.comments.store');
            Route::post('comments/destroy', [AppPublishingController::class, 'comments_destroy'])->name('app.publishing.comments.destroy');
            Route::post('preview_calendar', [AppPublishingController::class, 'preview_calendar'])->name('app.publishing.preview_calendar');
            Route::post('getLinkInfo', [AppPublishingController::class, 'getLinkInfo'])->name('app.publishing.getLinkInfo');
            Route::post('destroy', [AppPublishingController::class, 'destroy'])->name('app.publishing.destroy');
            Route::post('move_to_queue', [AppPublishingController::class, 'move_to_queue'])->name('app.publishing.move_to_queue');
            Route::post('destroy-by-filters', [AppPublishingController::class, 'destroyByFilter'])->name('app.publishing.destroy_by_filter');
            Route::post('save', [AppPublishingController::class, 'save'])->name('app.publishing.save');
            Route::post('changePostDate', [AppPublishingController::class, 'changePostDate'])->name('app.publishing.changePostDate');
			Route::get('/dashboard/today-counts', function() {
    $dayType = request()->day_type ?? 'daily';
    $teamId = request()->team_id;
    
    $counts = \PublishingReport::getTodayCounts($dayType, $teamId);
    
    return response()->json([
        'total_scheduled_post' => $counts['total_scheduled_post'],
        'inbox_messages' => $counts['inbox_messages'],
        'total_reviews' => $counts['total_reviews'],
        'new_people' => $counts['new_people'],
        'total_failed_post' => $counts['total_failed_post'],
        'total_holidays' => $counts['total_holidays'],
        'date_text_html' => $counts['date_range_text'],
        'day_type_text' => $counts['day_type_text'],
    ]);
})->name('dashboard.today-counts');

        });
    });
});

Route::get("app/publishing/cron", function (Request $request) {
    $key = $request->input('key');
    $cron_key = get_option("cron_key", rand_string());
    if ($key !== $cron_key) abort(403);
    app(\Modules\AppPublishing\Console\CronJobCommand::class)->handle();
    return "Cronjob executed.";
});