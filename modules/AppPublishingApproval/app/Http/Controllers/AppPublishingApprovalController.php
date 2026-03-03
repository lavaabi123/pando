<?php

namespace Modules\AppPublishingApproval\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppPublishing\Models\Posts;
use Illuminate\Pagination\Paginator;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;

class AppPublishingApprovalController extends Controller
{
    public function index()
    {
        return view('apppublishingapproval::index');
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
    ->where('p1.status', 2)
    ->where('p2.brand_id', session('brand_id'))
    ->where('p2.status', 2)
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

    public function pdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('pcre.backtrack_limit', '800000000');
        set_time_limit(300);

        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }
        $ids = is_array($ids) ? array_filter($ids, 'strlen') : [];

        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No posts selected'], 400);
        }

        $result = Posts::whereIn('id', $ids)->get();
        $result = $result->map(function ($row) {
            $row->social_networks_count = DB::table('posts')
                ->where('time_post', $row->time_post)
                ->where('grouping_data', $row->grouping_data)
                ->count();
            return $row;
        });

        $html = view('apppublishingapproval::pdf', [
			'result'     => $result,
			'brand_name' => session('brand_name', auth()->user()->name ?? 'Brand'),
		])->render();

		$pdf_name = session('brand_name', 'Brand') . '-Social-Media-Draft-' . date('Mj');

		$pdf = Pdf::loadHTML($html)
			->setPaper('a4', 'portrait')
			->setOptions([
				'isRemoteEnabled'    => true,
				'isHtml5ParserEnabled' => true,
				'defaultFont'        => 'sans-serif',
				'dpi'                => 96,
				'defaultMediaType'   => 'print',
			]);

		return $pdf->download($pdf_name . '.pdf');
    }
}
