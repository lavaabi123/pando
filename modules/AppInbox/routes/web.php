<?php

use Illuminate\Support\Facades\Route;
use Modules\AppInbox\Http\Controllers\InboxController;
use App\Helpers\Inbox_Helper;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your Inbox module.
|
*/

Route::group(["prefix" => "app"], function () {
	Route::group(["prefix" => "inbox"], function () {
		Route::get('/', [InboxController::class, 'index'])->name('inbox.index');
		Route::post('/ajax-list', [InboxController::class, 'ajaxList'])->name('inbox.ajax_list');
		Route::post('/ajax-list-detail', [InboxController::class, 'ajaxListDetail'])->name('inbox.ajax_list_detail');
		Route::post('/save-comment', [InboxController::class, 'saveComment'])->name('inbox.save_comment');
		Route::post('/delete-message', [InboxController::class, 'deleteMessage'])->name('inbox.delete_message');
		Route::post('/delete-message-bulk', [InboxController::class, 'deleteMessageBulk'])->name('inbox.delete_message_bulk');
		Route::post('/make-post-complete-selected', [InboxController::class, 'makePostCompleteSelected'])->name('inbox.make_post_complete_selected');
		Route::post('/make-post-complete-all', [InboxController::class, 'makePostCompleteAll'])->name('inbox.make_post_complete_all');
		Route::post('/make-post-incomplete-selected', [InboxController::class, 'makePostIncompleteSelected'])->name('inbox.make_post_incomplete_selected');
		Route::post('/make-post-incomplete-all', [InboxController::class, 'makePostIncompleteAll'])->name('inbox.make_post_incomplete_all');
		Route::post('/make-post-complete', [InboxController::class, 'makePostComplete'])->name('inbox.make_post_complete');
		Route::post('/make-post-uncomplete', [InboxController::class, 'makePostUncomplete'])->name('inbox.make_post_uncomplete');
		Route::post('/add-tag', [InboxController::class, 'addTag'])->name('inbox.add_tag');
		Route::post('/assign-tag', [InboxController::class, 'assignTag'])->name('inbox.assign_tag');
		Route::post('/set-favourite', [InboxController::class, 'setFavourite'])->name('inbox.set_favourite');
		Route::post('/assign-user', [InboxController::class, 'assignUser'])->name('inbox.assign_user');
		Route::get('/cron', [InboxController::class, 'cron'])->name('inbox.cron');
		Route::get('/inbox-count', function (Request $request) {
			$brandId = session('brand_id');
			$count = Inbox_Helper::getInboxCount($brandId);			
			return response()->json([
				'success' => true,
				'count' => $count
			]);
		})->name('inbox.count');
	});
});
Route::group(["prefix" => "app"], function () {
    Route::get('/brands/inbox-counts', function () {
        $userId = session('user_id');
        
        // Get role
        $role = DB::table('users')->where('id', $userId)->value('role');
        
        if ((int)$role === 2) {
            // SUPER ADMIN: get all brands
            $brandIds = DB::table('brands')->pluck('id')->toArray();
        } else {
            $memberRow = DB::table('team_members')
                ->select('team_id')
                ->where('uid', $userId)
                ->first();
            $isMember = (bool) $memberRow;
            $teamId = $isMember ? $memberRow->team_id : $userId;
            
            if (!$isMember) {
                // TEAM ADMIN
                $brandIds = DB::table('brands')
                    ->where('team_id', $teamId)
                    ->pluck('id')
                    ->toArray();
            } else {
                // TEAM MEMBER
                $brandIds = DB::table('brands as b')
                    ->leftJoin('user_brands as ub', function ($join) use ($userId, $teamId) {
                        $join->on('ub.brand_id', '=', 'b.id')
                             ->where('ub.user_id', '=', $userId)
                             ->where('ub.team_id', '=', $teamId);
                    })
                    ->where('b.team_id', $teamId)
                    ->where(function ($q) use ($userId) {
                        $q->where('b.user_id', $userId)
                          ->orWhereNotNull('ub.user_id');
                    })
                    ->distinct()
                    ->pluck('b.id')
                    ->toArray();
            }
        }
        
        $counts = Inbox_Helper::getInboxCountsByBrands($brandIds);
        
        return response()->json([
            'success' => true,
            'counts' => $counts
        ]);
    })->name('brands.inbox.counts');
});
