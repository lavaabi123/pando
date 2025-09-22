<?php

namespace Modules\AppAnalyticsTiktok\Services;

use Modules\AppChannels\Models\Accounts;
use Modules\AppAnalytics\Models\SocialAnalytics;
use Modules\AppAnalytics\Models\SocialAnalyticsSnapshot;
use Modules\AppAnalytics\Models\SocialAnalyticsPost;
use Modules\AppAnalytics\Models\SocialAnalyticsPostInfo;
use Modules\AppAnalytics\Contracts\SocialAnalyticsInterface;
use Carbon\Carbon;
use DB;

class TiktokAnalytics implements SocialAnalyticsInterface
{
    protected Facebook $fb;

    public function getName(): string
    {
        return 'Tiktok';
    }

    public function getAccounts(int $teamId)
	{
		$accounts = Accounts::where("team_id", $teamId)->where("social_network", "tiktok")->where("category", "profile")->orderBy('id')->get();

		if ($accounts) {
			foreach ($accounts as $key => $value) {
				$module = \Module::find($value->module);
				$moduleInfo = $module->get('menu');
				$accounts[$key]->module_icon = $moduleInfo['icon']??'';
				$accounts[$key]->module_color = $moduleInfo['color']??'';
				$accounts[$key]->module_name = $moduleInfo['name']??'';

			}
		}

		return $accounts;
	}

    public function getAnalyticsData(int $teamId, ?string $id_secure = null, ?string $since = null, ?string $until = null): array
    {
        $accountInfo = $this->getAccountInfo($teamId, $id_secure);

        if (!$accountInfo) {
            return [
                'status' => 'error',
                'message' => __('Tiktok account not found or disconnected.'),
            ];
        }

        $accountId = $accountInfo['id'];
        $openId = $accountInfo['pid'];
        $accessToken = $accountInfo['token'];

        // SYNC
        $this->syncAccountData($accountId, $accessToken, $openId, $since, $until);
        $this->syncPostInsights($accountId, $accessToken, $openId, $since, $until);

        return [
            'status' => 'success',
            'account' => $accountInfo,
            'overview' => $this->getOverview($accountId, $since, $until),
            'followerCountTrend' => $this->getFollowerCountTrend($accountId, $since, $until),
            'trendChartData' => $this->getTrendChartData($accountId, $since, $until),
            'viewTrendChartData' => $this->getViewTrendChartData($accountId, $since, $until),
            'engagementRateTrend' => $this->getEngagementRateTrend($accountId, $since, $until),
            'averageViewsPerVideoTrend' => $this->getAverageViewsPerVideoTrend($accountId, $since, $until),
            'postHistoryList' => $this->getPostHistoryList($accountId, $since, $until),


        ];
    }

    public function getOverview(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'view_count',
	        'like_count',
	        'comment_count',
	        'share_count',
	    ];

	    $data = SocialAnalyticsPostInfo::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$since, $until])
	        ->get()
	        ->groupBy('metric')
	        ->map(fn($group) => $group->sum('value'))
	        ->all();

	    $days = Carbon::parse($since)->diffInDays(Carbon::parse($until)) + 1;
	    $sinceCompare = Carbon::parse($since)->subDays($days)->toDateString();
	    $untilCompare = Carbon::parse($since)->subDay()->toDateString();

	    $dataCompare = SocialAnalyticsPostInfo::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->get()
	        ->groupBy('metric')
	        ->map(fn($group) => $group->sum('value'))
	        ->all();

	    $currentPosts = SocialAnalyticsPost::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereBetween('date', [$since, $until])
	        ->count();

	    $previousPosts = SocialAnalyticsPost::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->count();

	    $calculateChange = function ($current, $previous) {
	        if ($previous == 0 && $current == 0) return 0;
	        if ($previous == 0) return 100;
	        return round((($current - $previous) / $previous) * 100, 2);
	    };

	    $currentViews    = $data['view_count'] ?? 0;
	    $currentTotalER  = ($data['like_count'] ?? 0) + ($data['comment_count'] ?? 0) + ($data['share_count'] ?? 0);
	    $engagementRate  = $currentViews > 0 ? round(($currentTotalER / $currentViews) * 100, 2) : 0;

	    $previousViews   = $dataCompare['view_count'] ?? 0;
	    $previousTotalER = ($dataCompare['like_count'] ?? 0) + ($dataCompare['comment_count'] ?? 0) + ($dataCompare['share_count'] ?? 0);
	    $engagementRateCompare = $previousViews > 0 ? round(($previousTotalER / $previousViews) * 100, 2) : 0;

	    return [
	        'views' => [
	            'value' => (int) $currentViews,
	            'change' => $calculateChange($currentViews, $previousViews),
	        ],
	        'likes' => [
	            'value' => (int) ($data['like_count'] ?? 0),
	            'change' => $calculateChange($data['like_count'] ?? 0, $dataCompare['like_count'] ?? 0),
	        ],
	        'comments' => [
	            'value' => (int) ($data['comment_count'] ?? 0),
	            'change' => $calculateChange($data['comment_count'] ?? 0, $dataCompare['comment_count'] ?? 0),
	        ],
	        'shares' => [
	            'value' => (int) ($data['share_count'] ?? 0),
	            'change' => $calculateChange($data['share_count'] ?? 0, $dataCompare['share_count'] ?? 0),
	        ],
	        'published_videos' => [
	            'value' => $currentPosts,
	            'change' => $calculateChange($currentPosts, $previousPosts),
	        ],
	        'engagement_rate' => [
	            'value' => $engagementRate,
	            'change' => $calculateChange($engagementRate, $engagementRateCompare),
	        ],
	    ];
	}

	public function getFollowerCountTrend(int $accountId, string $since, string $until): array
	{
	    $days = collect(Carbon::parse($since)->daysUntil(Carbon::parse($until)))
	        ->map(fn($d) => $d->format('Y-m-d'))
	        ->values()
	        ->toArray();

	    $raw = SocialAnalytics::select(
	            DB::raw('DATE(date) as day'),
	            DB::raw('MAX(value) as value')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->where('metric', 'follower_count')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderBy('day')
	        ->get()
	        ->keyBy('day')
	        ->map(fn($r) => (int) $r->value);

	    $series = collect($days)->map(fn($day) => $raw[$day] ?? 0)->toArray();

	    $first = reset($series);
	    $last = end($series);
	    $change = ($first > 0) ? round((($last - $first) / $first) * 100, 2) : 0;

	    return [
	        'categories' => array_map(fn($d) => Carbon::parse($d)->format('M d'), $days),
	        'series' => [[
	            'name' => __('Followers'),
	            'data' => $series,
	        ]],
	        'summary' => [
	            'start' => $first,
	            'end' => $last,
	            'change' => $change,
	        ],
	    ];
	}

	public function getTrendChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = ['like_count', 'comment_count', 'share_count'];
	    $days = collect(Carbon::parse($since)->daysUntil(Carbon::parse($until)))
	        ->map(fn($d) => $d->format('Y-m-d'))
	        ->values()
	        ->toArray();

	    // === CURRENT PERIOD ===
	    $raw = SocialAnalyticsPostInfo::select(
	            DB::raw('DATE(date) as day'),
	            'metric',
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day', 'metric')
	        ->orderBy('day')
	        ->get();

	    $current = collect($raw)->groupBy('metric')->map(function ($rows) use ($days) {
	        $mapped = collect($rows)->keyBy('day')->map(fn($r) => (int) $r->total);
	        return collect($days)->map(fn($day) => $mapped[$day] ?? 0)->toArray();
	    });

	    // === PREVIOUS PERIOD ===
	    $daysCount = count($days);
	    $sincePrev = Carbon::parse($since)->subDays($daysCount)->toDateString();
	    $untilPrev = Carbon::parse($since)->subDay()->toDateString();

	    $rawPrev = SocialAnalyticsPostInfo::select(
	            'metric',
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$sincePrev, $untilPrev])
	        ->groupBy('metric')
	        ->get()
	        ->pluck('total', 'metric');

	    $summary = [
	        'total_likes'    => array_sum($current['like_count'] ?? []),
	        'total_comments' => array_sum($current['comment_count'] ?? []),
	        'total_shares'   => array_sum($current['share_count'] ?? []),
	    ];

	    $previous = [
	        'total_likes'    => (int) ($rawPrev['like_count'] ?? 0),
	        'total_comments' => (int) ($rawPrev['comment_count'] ?? 0),
	        'total_shares'   => (int) ($rawPrev['share_count'] ?? 0),
	    ];

	    $change = fn($now, $prev) => $prev == 0 ? ($now > 0 ? 100 : 0) : round((($now - $prev) / $prev) * 100, 2);

	    return [
	        'categories' => array_map(fn($d) => Carbon::parse($d)->format('M d'), $days),
	        'series' => [
			    ['name' => __('Likes'), 'data' => $current->get('like_count', collect($days)->map(fn() => 0)->toArray())],
			    ['name' => __('Comments'), 'data' => $current->get('comment_count', collect($days)->map(fn() => 0)->toArray())],
			    ['name' => __('Shares'), 'data' => $current->get('share_count', collect($days)->map(fn() => 0)->toArray())],
			],
	        'summary' => [
	            'total_likes'     => $summary['total_likes'],
	            'likes_change'    => $change($summary['total_likes'], $previous['total_likes']),
	            'total_comments'  => $summary['total_comments'],
	            'comments_change' => $change($summary['total_comments'], $previous['total_comments']),
	            'total_shares'    => $summary['total_shares'],
	            'shares_change'   => $change($summary['total_shares'], $previous['total_shares']),
	        ]
	    ];
	}

	public function getViewTrendChartData(int $accountId, string $since, string $until): array
	{
	    $days = collect(Carbon::parse($since)->daysUntil(Carbon::parse($until)))
	        ->map(fn($d) => $d->format('Y-m-d'))
	        ->values()
	        ->toArray();

	    // === CURRENT PERIOD ===
	    $raw = SocialAnalyticsPostInfo::select(
	            DB::raw('DATE(date) as day'),
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->where('metric', 'view_count')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderBy('day')
	        ->get()
	        ->keyBy('day')
	        ->map(fn($r) => (int) $r->total);

	    $viewSeries = collect($days)->map(fn($day) => $raw[$day] ?? 0)->toArray();

	    // === PREVIOUS PERIOD ===
	    $daysCount = count($days);
	    $sincePrev = Carbon::parse($since)->subDays($daysCount)->toDateString();
	    $untilPrev = Carbon::parse($since)->subDay()->toDateString();

	    $totalPrevViews = SocialAnalyticsPostInfo::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->where('metric', 'view_count')
	        ->whereBetween('date', [$sincePrev, $untilPrev])
	        ->sum('value');

	    $totalViews = array_sum($viewSeries);
	    $avgViews = count($viewSeries) > 0 ? round($totalViews / count($viewSeries), 2) : 0;

	    $calcChange = fn($now, $prev) =>
	        ($prev == 0)
	            ? ($now > 0 ? 100 : 0)
	            : round((($now - $prev) / $prev) * 100, 2);

	    return [
	        'categories' => array_map(fn($d) => Carbon::parse($d)->format('M d'), $days),
	        'series' => [[
	            'name' => 'Views',
	            'data' => $viewSeries,
	        ]],
	        'summary' => [
	            'total_views'        => $totalViews,
	            'avg_views_per_day'  => $avgViews,
	            'max_views'          => max($viewSeries),
	            'min_views'          => min($viewSeries),
	            'views_change'       => $calcChange($totalViews, $totalPrevViews),
	        ],
	    ];
	}

	public function getEngagementRateTrend(int $accountId, string $since, string $until): array
	{
	    $days = collect(Carbon::parse($since)->daysUntil(Carbon::parse($until)))
	        ->map(fn($d) => $d->format('Y-m-d'))
	        ->values()
	        ->toArray();

	    $metrics = ['view_count', 'like_count', 'comment_count', 'share_count'];

	    $raw = SocialAnalyticsPostInfo::select(
	            DB::raw('DATE(date) as day'),
	            'metric',
	            DB::raw('SUM(value) as total')
	        )
	        ->where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day', 'metric')
	        ->orderBy('day')
	        ->get();

	    $grouped = collect($raw)->groupBy('metric')->map(function ($rows) {
	        return $rows->keyBy('day')->map(fn($r) => (int) $r->total);
	    });

	    $engagementRates = [];
	    $totalLikes = 0;
	    $totalComments = 0;
	    $totalShares = 0;
	    $totalViews = 0;

	    foreach ($days as $day) {
	        $likes = $grouped['like_count'][$day] ?? 0;
	        $comments = $grouped['comment_count'][$day] ?? 0;
	        $shares = $grouped['share_count'][$day] ?? 0;
	        $views = $grouped['view_count'][$day] ?? 0;

	        $interactions = $likes + $comments + $shares;
	        $rate = ($views > 0) ? round(($interactions / $views) * 100, 2) : 0;

	        $engagementRates[] = $rate;

	        $totalLikes += $likes;
	        $totalComments += $comments;
	        $totalShares += $shares;
	        $totalViews += $views;
	    }

	    $overallRate = ($totalViews > 0)
	        ? round((($totalLikes + $totalComments + $totalShares) / $totalViews) * 100, 2)
	        : 0;

	    return [
	        'categories' => array_map(fn($d) => Carbon::parse($d)->format('M d'), $days),
	        'series' => [[
	            'name' => 'Engagement Rate (%)',
	            'data' => $engagementRates,
	        ]],
	        'summary' => [
	            'avg_engagement_rate' => $overallRate,
	            'max_engagement_rate' => count($engagementRates) ? max($engagementRates) : 0,
	            'min_engagement_rate' => count($engagementRates) ? min($engagementRates) : 0,
	        ]
	    ];
	}

	public function getAverageViewsPerVideoTrend(int $accountId, string $since, string $until): array
	{
	    $days = collect(Carbon::parse($since)->daysUntil(Carbon::parse($until)))
	        ->map(fn($d) => $d->format('Y-m-d'))
	        ->values()
	        ->toArray();

	    $posts = SocialAnalyticsPost::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereBetween('date', [$since, $until])
	        ->select('post_id', 'date')
	        ->get()
	        ->groupBy('date');

	    $views = SocialAnalyticsPostInfo::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->where('metric', 'view_count')
	        ->whereBetween('date', [$since, $until])
	        ->get()
	        ->groupBy('post_id')
	        ->map(fn($items) => $items->max('value'));

	    $dailyAvgViews = [];
	    foreach ($days as $day) {
	        $postIds = isset($posts[$day]) ? $posts[$day]->pluck('post_id')->toArray() : [];

	        $viewValues = collect($postIds)
	            ->map(fn($pid) => $views[$pid] ?? 0)
	            ->filter()
	            ->values();

	        $avg = $viewValues->count() > 0 ? round($viewValues->avg(), 2) : 0;

	        $dailyAvgViews[] = $avg;
	    }

	    $totalViews = array_sum($views->toArray());
	    $totalVideos = $views->count();
	    $overallAvg = $totalVideos > 0 ? round($totalViews / $totalVideos, 2) : 0;

	    return [
	        'categories' => array_map(fn($d) => Carbon::parse($d)->format('M d'), $days),
	        'series' => [[
	            'name' => __('Avg. Views/Video'),
	            'data' => $dailyAvgViews,
	        ]],
	        'summary' => [
	            'total_videos' => $totalVideos,
	            'total_views' => $totalViews,
	            'avg_views_per_video' => $overallAvg,
	            'max_views' => count($dailyAvgViews) ? max($dailyAvgViews) : 0,
	            'min_views' => count($dailyAvgViews) ? min($dailyAvgViews) : 0,
	        ]
	    ];
	}

	public function getPostHistoryList(int $accountId, string $since, string $until): array
	{
	    $posts = SocialAnalyticsPost::where('account_id', $accountId)
	        ->where('social_network', 'tiktok')
	        ->whereBetween('created_time', [$since, $until])
	        ->orderByDesc('created_time')
	        ->get();

	    $results = [];

	    foreach ($posts as $post) {
	        $metrics = SocialAnalyticsPostInfo::where('account_id', $accountId)
	            ->where('social_network', 'tiktok')
	            ->where('post_id', $post->post_id)
	            ->get()
	            ->groupBy('metric')
	            ->map(fn($items) => $items->sortByDesc('date')->first()->value ?? 0);

	        $views    = (int) ($metrics['view_count'] ?? 0);
	        $likes    = (int) ($metrics['like_count'] ?? 0);
	        $comments = (int) ($metrics['comment_count'] ?? 0);
	        $shares   = (int) ($metrics['share_count'] ?? 0);
	        $engagement = $likes + $comments + $shares;
	        $engagementRate = $views > 0 ? round(($engagement / $views) * 100, 2) : 0;

	        $results[] = [
	            'post_id'         => $post->post_id,
	            'message'         => \Str::limit($post->message, 100),
	            'media_url'       => $post->full_picture,
	            'permalink_url'   => $post->permalink_url,
	            'date'            => $post->date,
	            'created_time'    => $post->created_time,
	            'views'           => $views,
	            'likes'           => $likes,
	            'comments'        => $comments,
	            'shares'          => $shares,
	            'engagement_rate' => $engagementRate,
	        ];
	    }

	    return $results;
	}

    // -------------------------------------
	// SYNCING NEW ANALYTICS DATA
	// -------------------------------------
	protected function getAccountInfo(int $teamId, ?string $id_secure = null): ?array
	{
	    $account = Accounts::where('social_network', 'tiktok')
	        ->where('category', 'profile')
	        ->where('login_type', 1)
	        ->where('team_id', $teamId)
	        ->when($id_secure, fn($q) => $q->where('id_secure', $id_secure))
	        ->first();

	    if (!$account) {
	        logger()->warning("[TiktokAnalytics] No account found for team_id={$teamId}");
	        return null;
	    }

	    $now = time();
	    $tokenData = json_decode($account->token, true);

	    $tokens = $this->refreshAccessToken($tokenData['refresh_token'] ?? '');
	    if ($tokens && isset($tokens['access_token'])) {
	        $account->update([
	            'token' => json_encode($tokens),
	        ]);
	        $accessToken = $tokens['access_token'];
	    } else {
	        $accessToken = $tokenData['access_token'] ?? null;
	    }

	    if (!$accessToken) {
	        logger()->error("[TiktokAnalytics] Missing access_token after refresh.");
	        return null;
	    }

	    if (\Analytics::shouldFetchSocialAnalytics($account->id, 'tiktok', 'account')) {
	        try {
	            $profile = $this->getUserInfo($accessToken, $account->pid);

	            if (!$profile || !is_array($profile)) {
	                logger()->warning("[TiktokAnalytics] getUserInfo returned null or invalid.");
	                return null;
	            }

	            $info = [
	                'id'              => $account->id,
	                'pid'             => $account->pid,
	                'name'            => $profile['display_name'] ?? $account->name,
	                'username'        => $profile['username'] ?? $account->username,
	                'url'             => "https://www.tiktok.com/@{$profile['username']}",
	                'avatar'          => $profile['avatar_url'] ?? theme_public_asset('img/default.png'),
	                'followers_count' => $profile['follower_count'] ?? 0,
	                'video_count'     => $profile['video_count'] ?? 0,
	            ];

	            SocialAnalyticsSnapshot::updateOrCreate([
	                'account_id' => $account->id,
	                'social_network' => 'tiktok',
	                'date' => now()->toDateString(),
	            ], [
	                'data' => $info,
	                'created' => $now,
	            ]);

	            $accessToken = json_decode($account->token, true);
	            $info['token'] = $accessToken['access_token'];

	            \Analytics::markSynced($account->id, 'tiktok', 'account');
	            return $info;

	        } catch (\Exception $e) {
	            logger()->error("[TiktokAnalytics] getAccountInfo error: " . $e->getMessage());
	        }
	    }

	    $snapshot = SocialAnalyticsSnapshot::where([
	        'account_id' => $account->id,
	        'social_network' => 'tiktok',
	        'date' => now()->toDateString(),
	    ])->first();

	    if ($snapshot && $snapshot->data) {
	        logger()->info("[TiktokAnalytics] Using snapshot data from DB.");
	        $data = is_array($snapshot->data) ? $snapshot->data : json_decode($snapshot->data, true);
	        $accessToken = json_decode($account->token, true);
            $data['token'] = $accessToken['access_token'];
	        return $data;
	    }

	    return null;
	}
	
	public function syncAccountData(int $accountId, string $accessToken, string $openId, string $since, string $until): void
	{
		if (\Analytics::shouldFetchSocialAnalytics($accountId, 'tiktok', 'profile')) {
		    $date = Carbon::today()->toDateString();
		    $stats = $this->getUserStats($accessToken, $openId);
		    if (!$stats) return;

		    $metrics = [
		        'follower_count' => $stats['follower_count'] ?? 0,
		        'likes_count'    => $stats['likes_count'] ?? 0,
		        'video_count'    => $stats['video_count'] ?? 0,
		        'following_count'=> $stats['following_count'] ?? 0,
		    ];

		    foreach ($metrics as $metric => $value) {
			    if ($value > 0) {
			        SocialAnalytics::updateOrCreate([
			            'account_id'     => $accountId,
			            'social_network' => 'tiktok',
			            'metric'         => $metric,
			            'date'           => $date,
			        ], [
			            'value' => $value,
			        ]);
			    }
			}

			\Analytics::markSynced($accountId, 'tiktok', 'profile');
		}
	}

    public function syncPostInsights(int $accountId, string $accessToken, string $openId, string $since, string $until): void
	{
		if (\Analytics::shouldFetchSocialAnalytics($accountId, 'tiktok', 'post')) {
		    $videos = $this->getVideoList($accessToken, $openId);

		    foreach ($videos as $video) {
		        $createdAt = Carbon::parse($video['create_time'] ?? now())->toDateString();
		        $post = SocialAnalyticsPost::updateOrCreate([
				    'post_id'       => $video['id'],
				    'account_id'    => $accountId,
				    'social_network'=> 'tiktok',
				    'date'          => now()->toDateString(),
				], [
				    'created_time'  => $createdAt,
				    'message'       => $video['video_description'] ?? '',
				    'media_url'     => $video['cover_image_url'] ?? '',
				    'permalink_url' => 'https://www.tiktok.com/@video/video/' . $video['id'],
				    'snapshot'      => $video,
				]);

				$postId = $video['id'];

				$metrics = [
				    'like_count'    => $video['like_count'] ?? 0,
				    'comment_count' => $video['comment_count'] ?? 0,
				    'share_count'   => $video['share_count'] ?? 0,
				    'view_count'    => $video['view_count'] ?? 0,
				];

				foreach ($metrics as $metric => $value) {
				    if ($value > 0) {
				        SocialAnalyticsPostInfo::updateOrCreate([
				            'account_id'     => $accountId,
				            'post_id'        => $video['id'],
				            'social_network' => 'tiktok',
				            'date'           => now()->toDateString(),
				            'metric'         => $metric,
				        ], [
				            'value' => $value,
				        ]);
				    }
				}

		    }

		    \Analytics::markSynced($accountId, 'tiktok', 'post');
		}
	}

    protected function getUserStats(string $accessToken, string $openId): ?array
	{
	    $stats = $this->getUserInfo($accessToken, $openId);

        if (is_array($stats)) {
            return [
                'follower_count'   => $stats['follower_count'] ?? 0,
                'likes_count'      => $stats['likes_count'] ?? 0,
                'video_count'      => $stats['video_count'] ?? 0,
                'following_count'  => $stats['following_count'] ?? 0,
            ];
        }

	    return null;
	}

    protected function getUserInfo(string $accessToken, string $openId): ?array
	{
	    $response = \Http::withHeaders([
	        'Authorization' => 'Bearer ' . $accessToken,
	        'Content-Type' => 'application/json',
	    ])->get('https://open.tiktokapis.com/v2/user/info/', [
	        'fields' => 'display_name,username,avatar_url,follower_count,following_count,likes_count,video_count',
	        'open_id' => $openId,
	    ]);

	    if ($response->successful()) {
	        return $response->json('data.user');
	    }

	    logger()->warning('[TiktokAnalytics] Failed to fetch user info', [
	        'status' => $response->status(),
	        'body' => $response->body(),
	    ]);

	    return null;
	}

	protected function getVideoList(string $accessToken, string $openId): array
	{
	    $videos = [];
	    $cursor = 0;
	    do {
			
	    	$payload = [
	            'cursor'    => $cursor,
	            'max_count' => 20,
	        ];

	        $fields = [
		        'id',
		        'create_time',
		        'cover_image_url',
		        'share_url',
		        'video_description',
		        'duration',
		        'height',
		        'width',
		        'title',
		        'embed_html',
		        'embed_link',
		        'like_count',
		        'comment_count',
		        'share_count',
		        'view_count',
		    ];

	        $response = \Http::withHeaders([
	            'Authorization' => 'Bearer ' . $accessToken,
	            'Content-Type'  => 'application/json',
	        ])->post('https://open.tiktokapis.com/v2/video/list/?fields='.implode(',', $fields), $payload);

	        if (!$response->successful()) {
	            logger()->error('[TiktokAnalytics] getVideoList failed', [
	                'status' => $response->status(),
	                'body'   => $response->body(),
	            ]);
	            break;
	        }

	        $json = $response->json();
	        $batch = $json['data']['videos'] ?? [];
	        $videos = array_merge($videos, $batch);

	        $cursor = $json['data']['cursor'] ?? 0;
	        $hasMore = $json['data']['has_more'] ?? false;

	    } while ($hasMore);

	    return $videos;
	}

	protected function refreshAccessToken(string $refreshToken): ?array
	{
	    $appId = get_option("tiktok_app_id");
	    $appSecret = get_option("tiktok_app_secret");

	    $response = \Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
	        'client_key'    => $appId,
	        'client_secret' => $appSecret,
	        'grant_type'    => 'refresh_token',
	        'refresh_token' => $refreshToken,
	    ]);

	    if ($response->successful()) {
	        return $response->json();
	    }

	    logger()->error('[TikTok] Failed to refresh access token', [
	        'status' => $response->status(),
	        'body' => $response->body(),
	    ]);

	    return null;
	}

}
