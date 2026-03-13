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
        $brandId = session('brand_id');

        // Get the brand's team_id and owner user_id
        $brand = DB::table('brands')->where('id', $brandId)->select('team_id', 'user_id')->first();
        $teamId = $brand ? $brand->team_id : null;

        // Users = brand owner + all team_members under that team
        // team_members: uid (user id), team_id
        $userIds = collect();
        if ($brand) {
            $userIds->push($brand->user_id); // brand owner
        }
        if ($teamId) {
            $memberIds = DB::table('team_members')
                ->where('team_id', $teamId)
                ->whereNotNull('uid')
                ->pluck('uid');
            $userIds = $userIds->merge($memberIds);
        }
        $userIds = $userIds->unique()->filter()->values();

        $users = DB::table('users')
            ->whereIn('id', $userIds)
            ->select('id', 'fullname')
            ->orderBy('fullname')
            ->get();

        // Accounts (profiles) under this brand — accounts table has: id, name, social_network, brand_id, can_post, status
        $accounts = DB::table('accounts')
            ->where('brand_id', $brandId)
            ->where('status', 1)
            ->where('can_post', 1)
            ->select('id', 'name', 'social_network')
            ->orderBy('social_network')
            ->get();

        return view('apppublishingapproval::index', compact('users', 'accounts'));
    }

public function list(Request $request)
    {
        $search          = $request->input('keyword');
        $from            = $request->input('from', '');
        $current_page    = (int) $request->input('page', 0) + 1;
        $per_page        = 30;
        $brandId         = session('brand_id');

        // Filter params (matching CI4 names)
        $approvalOrder    = $request->input('approval_order');
        $approvalStatus   = $request->input('approval_status');
        $approvalAssignee = $request->input('approval_assignee');
        $approvalProfile  = $request->input('approval_profile');

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        // ── Step 1: Find qualifying grouping_data values ───────────────────────
        // We filter on the representative post (p1 = one post per group).
        // For approval_profile we need EXISTS in the sibling posts.
        // This ensures filtering works correctly across grouped posts.
        $groupFilter = DB::table('posts as p1')
            ->select('p1.grouping_data')
            ->where('p1.brand_id', $brandId)
            ->where('p1.status', 2)

            // approval_status filter — applied on p1 (any post in group shares time_post/user_id)
            ->when($approvalStatus === 'Missing schedule', function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNull('p1.time_post')
                       ->orWhere('p1.time_post', '<', now()->timestamp);
                });
            })
            ->when($approvalStatus === 'Unassigned posts', function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNull('p1.user_id')->orWhere('p1.user_id', '');
                });
            })
            // 'Waiting for approval' = status=2 is already the condition, no extra filter

            // approval_assignee — user_id is on p1 directly
            ->when($approvalAssignee, function ($q) use ($approvalAssignee) {
                $q->where('p1.user_id', $approvalAssignee);
            })

            // approval_profile — must check if ANY sibling post uses that account_id
            // Using EXISTS so we don't fan out/duplicate grouping_data rows
            ->when($approvalProfile, function ($q) use ($approvalProfile, $brandId) {
                $q->whereExists(function ($sub) use ($approvalProfile, $brandId) {
                    $sub->select(DB::raw(1))
                        ->from('posts as p_sib')
                        ->whereColumn('p_sib.grouping_data', 'p1.grouping_data')
                        ->where('p_sib.account_id', $approvalProfile)
                        ->where('p_sib.brand_id', $brandId)
                        ->where('p_sib.status', 2);
                });
            })

            // search
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('p1.data', 'like', '%'.$search.'%');
                });
            })

            ->groupBy('p1.grouping_data');

        // ── Step 2: For the qualifying groups, build the display subquery ──────
        // Collect avatars/urls/social_networks per group by joining sibling posts
        $qualifyingGroups = $groupFilter->pluck('grouping_data');

        if ($qualifyingGroups->isEmpty()) {
            // No results — return early but still render view with empty paginator
            $schedules = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, $per_page, $current_page
            );
            return response()->json([
                'status' => 1,
                'data'   => view(module('key').'::list', [
                    'schedules' => $schedules,
                    'from'      => $from,
                ])->render(),
            ]);
        }

        $subquery = DB::table('posts as p1')
            ->select(
                'p1.grouping_data',
                DB::raw('MAX(p1.id) as latest_post_id'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(a.id,":::",COALESCE(a.avatar,"")) ORDER BY a.id SEPARATOR "|||") as avatars'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(a.id,":::",COALESCE(a.url,""))     ORDER BY a.id SEPARATOR "|||") as urls'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(a.id,":::",COALESCE(a.social_network,"")) ORDER BY a.id SEPARATOR "|||") as social_networks')
            )
            ->leftJoin('posts as p2', function ($join) use ($brandId) {
                $join->on('p2.grouping_data', '=', 'p1.grouping_data')
                     ->where('p2.brand_id', $brandId)
                     ->where('p2.status', 2);
            })
            ->leftJoin('accounts as a', 'p2.account_id', '=', 'a.id')
            ->where('p1.brand_id', $brandId)
            ->where('p1.status', 2)
            ->whereIn('p1.grouping_data', $qualifyingGroups)
            ->groupBy('p1.grouping_data');

        // ── Step 3: Main paginated query ──────────────────────────────────────
        $query = Posts::select('posts.*', 'grouped.avatars', 'grouped.urls', 'grouped.social_networks',
                               'u.fullname as creator_name')
            ->joinSub($subquery, 'grouped', function ($join) {
                $join->on('posts.id', '=', 'grouped.latest_post_id');
            })
            ->leftJoin('users as u', 'posts.user_id', '=', 'u.id');

        // Order
        if ($approvalOrder === 'By Date Asc') {
            $query->orderBy('posts.changed', 'ASC');
        } elseif ($approvalOrder === 'By Date Desc') {
            $query->orderBy('posts.changed', 'DESC');
        } else {
            $query->orderByDesc('posts.changed');
        }

        $schedules = $query->paginate($per_page);

        if ($schedules->total() == 0 && $current_page > 1) {
            return response()->json([
                'status'  => 0,
                'message' => __('No data found.'),
            ]);
        }

        // ── Step 4: Eager-load comments ───────────────────────────────────────
        $groupingIds = $schedules->pluck('grouping_data')->filter()->unique()->values();

        $commentsByGroup = DB::table('post_comments as c')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->whereIn('c.grouping_data', $groupingIds)
            ->select('c.*', 'u.fullname as user_name')
            ->orderBy('c.created_at')
            ->get()
            ->groupBy('grouping_data');

        $schedules->each(function ($post) use ($commentsByGroup) {
            $post->comments = $commentsByGroup->get($post->grouping_data, collect());
        });

        return response()->json([
            'status' => 1,
            'data'   => view(module('key').'::list', [
                'schedules' => $schedules,
                'from'      => $from,
            ])->render(),
        ]);
    }

    public function destroy(Request $request)
    {
        $ids = $request->input('id', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No ids provided'], 400);
        }

        // For each provided id, delete all posts in the same grouping_data
        foreach ($ids as $id) {
            $post = DB::table('posts')->where('id', $id)->first();
            if ($post) {
                DB::table('posts')->where('grouping_data', $post->grouping_data)->delete();
            }
        }

        return response()->json(['status' => 'success', 'message' => __('Deleted successfully.')]);
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

        $result = Posts::whereIn('posts.id', $ids)
            ->leftJoin('accounts as a', 'posts.account_id', '=', 'a.id')
            ->select('posts.*', 'a.avatar', 'a.social_network')
            ->get();
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

        $pdf_name = session('brand_name', 'Brand').'-Social-Media-Draft-'.date('Mj');

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'     => true,
                'isHtml5ParserEnabled'=> true,
                'defaultFont'         => 'sans-serif',
                'dpi'                 => 96,
                'defaultMediaType'    => 'print',
            ]);

        return $pdf->download($pdf_name.'.pdf');
    }
}