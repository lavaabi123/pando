<?php

namespace Modules\AppPublishingDraft\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppPublishing\Models\Posts;
use Illuminate\Pagination\Paginator;
use DB;

class AppPublishingDraftController extends Controller
{
    public function index()
    {
        return view('apppublishingdraft::index');
    }

    public function list(Request $request)
    {
        $search = $request->input('keyword');
        $status = $request->input('status');
		$from = $request->input('from') ?? '';
        $current_page = (int) $request->input('page', 0) + 1;
        $per_page = 30;

        $teamId = $request->input('team_id') ?? (auth()->user()->team_id ?? null);

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        // Get grouping_data and their avatars
$subquery = DB::table('posts as p1')
    ->select(
    'p1.grouping_data',
    DB::raw('MAX(p1.id) as latest_post_id'),
    DB::raw('GROUP_CONCAT(DISTINCT CONCAT(a.id, ":::", a.avatar) ORDER BY a.id SEPARATOR "|||") as avatars'),
    DB::raw('GROUP_CONCAT(DISTINCT CONCAT(a.id, ":::", a.url) ORDER BY a.id SEPARATOR "|||") as urls'),
    DB::raw('GROUP_CONCAT(DISTINCT CONCAT(a.id, ":::", a.social_network) ORDER BY a.id SEPARATOR "|||") as social_networks')
)
    ->leftJoin('posts as p2', 'p1.grouping_data', '=', 'p2.grouping_data')
    ->leftJoin('accounts as a', 'p2.account_id', '=', 'a.id')
    ->where('p1.brand_id', session('brand_id'))
    ->where('p1.status', 1)
    ->where('p2.brand_id', session('brand_id'))
    ->where('p2.status', 1)
    ->when($search, function ($q) use ($search) {
        $q->where(function ($query) use ($search) {
            $query->where('p1.url', 'like', '%' . $search . '%')
                  ->orWhere('p1.title', 'like', '%' . $search . '%')
                  ->orWhere('p1.desc', 'like', '%' . $search . '%');
        });
    })
    ->when($status !== null && $status !== '', function ($q) use ($status) {
        $q->where('p1.status', (int) $status);
    })
    ->groupBy('p1.grouping_data');

// Join with actual posts
$schedules = Posts::select('posts.*', 'grouped.avatars', 'grouped.urls', 'grouped.social_networks')
    ->joinSub($subquery, 'grouped', function ($join) {
        $join->on('posts.id', '=', 'grouped.latest_post_id');
    })
    ->orderByDesc('posts.changed')
    ->paginate($per_page);
		
//echo "<pre>";print_r($schedules);exit;		
        if ($schedules->total() == 0 && $current_page > 1) {
            return response()->json([
                "status" => 0,
                "message" => __("No data found."),
            ]);
        }

        return response()->json([
            "status" => 1,
            "data" => view(module('key') . '::list', [
                "schedules" => $schedules,
				"from" => $from
            ])->render()
        ]);
    } 
}
