<?php

namespace Modules\AppAnalyticsLinkedin\Services;

use Illuminate\Support\Facades\Http;
use Modules\AppChannels\Models\Accounts;
use Modules\AppAnalytics\Contracts\SocialAnalyticsInterface;
use Modules\AppAnalytics\Models\SocialAnalytics;
use Modules\AppAnalytics\Models\SocialAnalyticsPost;
use Modules\AppAnalytics\Models\SocialAnalyticsPostInfo;
use Carbon\Carbon;
use DB;

class LinkedinAnalytics implements SocialAnalyticsInterface
{
    protected string $apiBase = 'https://api.linkedin.com/v2/';

    public function getName(): string
    {
        return 'LinkedIn';
    }

    public function getAccounts(int $teamId)
	{
		$accounts = Accounts::where("team_id", $teamId)->where("social_network", "linkedin")->where('login_type', 1)->where("category", "page")->orderBy('id')->get();

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
        $account = Accounts::where('team_id', $teamId)
            ->where('social_network', 'linkedin')
            ->where('category', 'page')
	        ->where('login_type', 1)
	        ->where('team_id', $teamId)
            ->when($id_secure, fn ($q) => $q->where('id_secure', $id_secure))
            ->firstOrFail();

        $this->syncLinkedinData($account->id, $account->pid, $account->token, $since, $until);
        
        $overview = $this->getLinkedinOverview($account->id, $since, $until);
        $fansLocationMapChart = $this->getFansLocationMapChartData($account->id, $since, $until);
        $topFansCountries = $this->getTopFansCountries($account->id, $since, $until);
        $dailyAllPageViewsChart = $this->getDailyAllPageViewsChartData($account->id, $since, $until);
        $sectionPageViewsChartData = $this->getSectionPageViewsChartData($account->id, $since, $until);
        $devicePageViewsChartData = $this->getDevicePageViewsChartData($account->id, $since, $until);
        $postCountByDayChartData = $this->getPostCountByDayChartData($account->id, $since, $until);
        $interactionBreakdownChartData = $this->getInteractionBreakdownChartData($account->id, $since, $until);
        $clickCountChartData = $this->getClickCountChartData($account->id, $since, $until);
        $postImpressionAndEngagementChartData = $this->getPostImpressionAndEngagementChartData($account->id, $since, $until);
        $reachChartData = $this->getReachChartData($account->id, $since, $until);

        return [
            'status' => 'success',
            'account' => $account,
            'overview' => $overview,
            'fansLocationMapChart' => $fansLocationMapChart,
            'topFansCountries' => $topFansCountries,
            'dailyAllPageViewsChart' => $dailyAllPageViewsChart,
            'sectionPageViewsChartData' => $sectionPageViewsChartData,
            'devicePageViewsChartData' => $devicePageViewsChartData,
            'postCountChart' => $postCountByDayChartData,
            'interactionBreakdownChartData' => $interactionBreakdownChartData,
            'postImpressionAndEngagementChartData' => $postImpressionAndEngagementChartData,
            'clickCountChartData' => $clickCountChartData,
            'reachChartData' => $reachChartData,
        ];
    }

	public function getLinkedinOverview(int $accountId, string $since, string $until): array
	{
	    $metrics = [
	        'follower_count',
	        'impression_count',
	        'unique_impressions_count',
	        'engagement',
	        'page_view_all_page_views',
	    ];

	    $current = SocialAnalytics::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('metric')
	        ->pluck('total', 'metric');

	    $currentFollowers = (int) SocialAnalytics::query()
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->where('metric', 'follower_count')
	        ->whereBetween('date', [$since, $until])
	        ->orderByDesc('date')
	        ->value('value');

	    $totalPublishCurrent = DB::table('social_analytics_posts')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereBetween('date', [$since, $until])
	        ->count();

	    // Previous range
	    $days = Carbon::parse($since)->diffInDays(Carbon::parse($until)) + 1;
	    $sinceCompare = Carbon::parse($since)->subDays($days)->toDateString();
	    $untilCompare = Carbon::parse($since)->subDay()->toDateString();

	    $previous = SocialAnalytics::query()
	        ->select('metric', DB::raw('SUM(value) as total'))
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereIn('metric', $metrics)
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->groupBy('metric')
	        ->pluck('total', 'metric');

	    $previousFollowers = (int) SocialAnalytics::query()
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->where('metric', 'follower_count')
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->orderByDesc('date')
	        ->value('value');

	    $totalPublishPrevious = DB::table('social_analytics_posts')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereBetween('date', [$sinceCompare, $untilCompare])
	        ->count();

	    $calculateChange = function ($current, $previous) {
	        if ($previous == 0 && $current == 0) return 0;
	        if ($previous == 0) return 100;
	        return round((($current - $previous) / $previous) * 100, 2);
	    };

	    return [
	        'followers' => [
	            'value' => $currentFollowers,
	            'change' => $calculateChange($currentFollowers, $previousFollowers),
	        ],
	        'impressions' => [
	            'value' => (int) ($current['impression_count'] ?? 0),
	            'change' => $calculateChange($current['impression_count'] ?? 0, $previous['impression_count'] ?? 0),
	        ],
	        'reach' => [
	            'value' => (int) ($current['unique_impressions_count'] ?? 0),
	            'change' => $calculateChange($current['unique_impressions_count'] ?? 0, $previous['unique_impressions_count'] ?? 0),
	        ],
	        'engagement' => [
	            'value' => (int) ($current['engagement'] ?? 0),
	            'change' => $calculateChange($current['engagement'] ?? 0, $previous['engagement'] ?? 0),
	        ],
	        'page_views' => [
	            'value' => (int) ($current['page_view_all_page_views'] ?? 0),
	            'change' => $calculateChange($current['page_view_all_page_views'] ?? 0, $previous['page_view_all_page_views'] ?? 0),
	        ],
	        'published_posts' => [
	            'value' => $totalPublishCurrent,
	            'change' => $calculateChange($totalPublishCurrent, $totalPublishPrevious),
	        ],
	    ];
	}

	public function getDailyAllPageViewsChartData(int $accountId, string $since, string $until): array
	{
	    $allDays = collect();
	    $date = Carbon::parse($since);
	    $end = Carbon::parse($until);
	    while ($date->lte($end)) {
	        $allDays->push($date->format('M d'));
	        $date->addDay();
	    }

	    $raw = DB::table('social_analytics')
	        ->selectRaw('DATE_FORMAT(date, "%b %d") as day, SUM(value) as total')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->where('metric', 'page_view_all_page_views')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderByRaw('STR_TO_DATE(day, "%b %d")')
	        ->get()
	        ->keyBy('day');

	    $values = $allDays->map(fn($day) => (int) ($raw[$day]->total ?? 0))->toArray();

	    return [
	        'categories' => $allDays->toArray(),
	        'series' => [
	            ['name' => __('Page Views'), 'data' => $values]
	        ],
	        'summary' => [
	            'total' => array_sum($values)
	        ]
	    ];
	}

	public function getSectionPageViewsChartData(int $accountId, string $since, string $until): array
	{
	    $sections = ['overview', 'jobs', 'products', 'about'];
	    $days = collect();
	    $series = [];
	    $summary = [];
	    $allValues = [];

	    $dateCursor = Carbon::parse($since);
	    $endDate = Carbon::parse($until);
	    while ($dateCursor->lte($endDate)) {
	        $days->push($dateCursor->format('M d'));
	        $dateCursor->addDay();
	    }

	    foreach ($sections as $section) {
	        $raw = DB::table('social_analytics')
	            ->selectRaw('DATE_FORMAT(date, "%b %d") as day, SUM(value) as total')
	            ->where('account_id', $accountId)
	            ->where('social_network', 'linkedin')
	            ->where('metric', "page_view_{$section}_page_views")
	            ->whereBetween('date', [$since, $until])
	            ->groupBy('day')
	            ->orderByRaw('STR_TO_DATE(day, "%b %d")')
	            ->get()
	            ->keyBy('day');

	        $data = [];
	        foreach ($days as $day) {
	            $value = isset($raw[$day]) ? (int) $raw[$day]->total : 0;
	            $data[] = $value;
	            $allValues[] = $value;
	        }

	        $series[] = [
	            'name' => ucfirst($section),
	            'data' => $data,
	        ];

	        $summary[$section] = array_sum($data);
	    }

	    $summary['total'] = array_sum($allValues);

	    return [
	        'categories' => $days->toArray(),
	        'series' => $series,
	        'summary' => $summary,
	    ];
	}

	public function getDevicePageViewsChartData(int $accountId, string $since, string $until): array
	{
	    $deviceMetrics = [
	        'mobile'  => 'page_view_all_mobile_page_views',
	        'desktop' => 'page_view_all_desktop_page_views',
	    ];

	    $data = [];
	    $summary = [];

	    foreach ($deviceMetrics as $device => $metric) {
	        $value = (int) DB::table('social_analytics')
	            ->where('account_id', $accountId)
	            ->where('social_network', 'linkedin')
	            ->where('metric', $metric)
	            ->whereBetween('date', [$since, $until])
	            ->sum('value');

	        $data[] = [
	            'name' => __(ucfirst($device)),
	            'y'    => $value,
	        ];

	        $summary[$device] = $value;
	    }

	    $summary['total'] = array_sum(array_column($data, 'y'));

	    return [
	        'data' => $data,
	        'summary' => $summary,
	    ];
	}

	public function getFansLocationMapChartData(int $accountId, string $since, string $until): array
	{
	    $raw = DB::table('social_analytics')
	        ->selectRaw("REPLACE(metric, 'page_fans_country.', '') as country_code, MAX(value) as fans")
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereBetween('date', [$since, $until])
	        ->where('metric', 'like', 'page_fans_country.%')
	        ->groupBy('country_code')
	        ->get();

	    return $raw->map(fn($row) => [
	        'code' => strtoupper(substr($row->country_code, 0, 2)),
	        'value' => (int) $row->fans,
	    ])->values()->toArray();
	}

	public function getTopFansCountries(int $accountId, string $since, string $until): array
	{
	    $raw = DB::table('social_analytics')
	        ->selectRaw("REPLACE(metric, 'page_fans_country.', '') as code, MAX(value) as fans")
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereBetween('date', [$since, $until])
	        ->where('metric', 'like', 'page_fans_country.%')
	        ->groupBy('code')
	        ->orderByDesc('fans')
	        ->limit(10)
	        ->get();

	    return $raw->map(function ($row) {
	        return [
	            'country' => list_countries(strtoupper($row->code)),
	            'code'    => strtoupper($row->code),
	            'fans'    => (int) $row->fans,
	        ];
	    })->toArray();
	}

	public function getPostCountByDayChartData(int $accountId, string $since, string $until): array
	{
	    $raw = DB::table('social_analytics_posts')
	        ->selectRaw('DATE_FORMAT(date, "%Y-%m-%d") as day, COUNT(*) as total')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->orderByRaw('day')
	        ->get()
	        ->keyBy('day');

	    $start = \Carbon\Carbon::parse($since);
	    $end = \Carbon\Carbon::parse($until);
	    $categories = [];
	    $values = [];

	    while ($start->lte($end)) {
	        $dayLabel = $start->format('M d');
	        $dayKey = $start->format('Y-m-d');
	        $categories[] = $dayLabel;
	        $values[] = (int) ($raw[$dayKey]->total ?? 0);
	        $start->addDay();
	    }

	    return [
	        'categories' => $categories,
	        'series' => [
	            ['name' => __('Posts'), 'data' => $values]
	        ],
	        'summary' => [
	            'total' => array_sum($values)
	        ]
	    ];
	}

	public function getPostImpressionAndEngagementChartData(int $accountId, string $since, string $until): array
	{
	    $allDays = collect();
	    $date = Carbon::parse($since);
	    $end = Carbon::parse($until);
	    while ($date->lte($end)) {
	        $allDays->push($date->format('M d'));
	        $date->addDay();
	    }

	    $raw = DB::table('social_analytics')
	        ->selectRaw('DATE_FORMAT(date, "%b %d") as day, 
	                     SUM(CASE WHEN metric = "impression_count" THEN value ELSE 0 END) as impressions,
	                     SUM(CASE WHEN metric = "engagement" THEN value ELSE 0 END) as engagement')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->whereBetween('date', [$since, $until])
	        ->whereIn('metric', ['impression_count', 'engagement'])
	        ->groupBy('day')
	        ->orderByRaw('STR_TO_DATE(day, "%b %d")')
	        ->get()
	        ->keyBy('day');

	    $impressions = [];
	    $engagement = [];

	    foreach ($allDays as $day) {
	        $impressions[] = (int) ($raw[$day]->impressions ?? 0);
	        $engagement[] = (int) ($raw[$day]->engagement ?? 0);
	    }

	    $totalImpressions = array_sum($impressions);
	    $totalEngagement = array_sum($engagement);
	    $rate = $totalImpressions > 0 ? round(($totalEngagement / $totalImpressions) * 100, 2) : 0;

	    return [
	        'categories' => $allDays->toArray(),
	        'series' => [
	            ['name' => __('Impressions'), 'type' => 'column', 'data' => $impressions],
	            ['name' => __('Engagement'), 'type' => 'line', 'data' => $engagement],
	        ],
	        'summary' => [
	            'total_impressions' => $totalImpressions,
	            'total_engagement' => $totalEngagement,
	            'engagement_rate' => $rate
	        ]
	    ];
	}

	public function getReachChartData(int $accountId, string $since, string $until): array
	{
	    $dateRange = collect();
	    $start = Carbon::parse($since);
	    $end = Carbon::parse($until);
	    while ($start->lte($end)) {
	        $dateRange->push($start->format('M d'));
	        $start->addDay();
	    }

	    $raw = DB::table('social_analytics')
	        ->selectRaw('DATE_FORMAT(date, "%b %d") as day, SUM(value) as total')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->where('metric', 'unique_impressions_count')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->pluck('total', 'day');

	    $values = $dateRange->map(fn($day) => (int) ($raw[$day] ?? 0))->toArray();

	    return [
	        'categories' => $dateRange->toArray(),
	        'series' => [
	            ['name' => __('Reach'), 'data' => $values]
	        ],
	        'summary' => [
	            'total' => array_sum($values)
	        ]
	    ];
	}

	public function getInteractionBreakdownChartData(int $accountId, string $since, string $until): array
	{
	    $metrics = ['comment_count', 'like_count', 'share_count'];
	    $dateRange = collect();
	    $start = Carbon::parse($since);
	    $end = Carbon::parse($until);
	    while ($start->lte($end)) {
	        $dateRange->push($start->format('M d'));
	        $start->addDay();
	    }

	    $series = [];
	    $summary = [];

	    foreach ($metrics as $metric) {
	        $raw = DB::table('social_analytics')
	            ->selectRaw('DATE_FORMAT(date, "%b %d") as day, SUM(value) as total')
	            ->where('account_id', $accountId)
	            ->where('social_network', 'linkedin')
	            ->where('metric', $metric)
	            ->whereBetween('date', [$since, $until])
	            ->groupBy('day')
	            ->pluck('total', 'day');

	        $data = $dateRange->map(fn($day) => (int) ($raw[$day] ?? 0))->toArray();
	        $series[] = [
	            'name' => __(ucfirst(str_replace('_count', '', $metric))),
	            'data' => $data
	        ];
	        $summary[$metric] = array_sum($data);
	    }

	    $summary['total'] = array_sum($summary);

	    return [
	        'categories' => $dateRange->toArray(),
	        'series'     => $series,
	        'summary'    => $summary,
	    ];
	}

	public function getClickCountChartData(int $accountId, string $since, string $until): array
	{
	    $dateRange = collect();
	    $start = \Carbon\Carbon::parse($since);
	    $end = \Carbon\Carbon::parse($until);

	    while ($start->lte($end)) {
	        $dateRange->push($start->format('M d'));
	        $start->addDay();
	    }

	    $raw = DB::table('social_analytics')
	        ->selectRaw('DATE_FORMAT(date, "%b %d") as day, SUM(value) as total')
	        ->where('account_id', $accountId)
	        ->where('social_network', 'linkedin')
	        ->where('metric', 'click_count')
	        ->whereBetween('date', [$since, $until])
	        ->groupBy('day')
	        ->pluck('total', 'day'); // [day => total]

	    $values = $dateRange->map(fn($day) => (int) ($raw[$day] ?? 0))->toArray();

	    return [
	        'categories' => $dateRange->toArray(),
	        'series' => [[
	            'name' => __('Clicks'),
	            'data' => $values
	        ]],
	        'summary' => ['total' => array_sum($values)],
	    ];
	}

	/*
	* SYNC DATA	
	 */
    protected function syncLinkedinData(int $accountId, string $organizationId, string $accessToken, string $since, string $until): void
	{
	    if (\Analytics::shouldFetchSocialAnalytics($accountId, 'linkedin', 'page')) {
	        $this->syncFollowerStatistics($accountId, $organizationId, $accessToken, $since, $until);
	        $this->syncPostsAndInsights($accountId, $organizationId, $accessToken, $since, $until);
	        $this->syncOrganizationPostSummary($accountId, $organizationId, $accessToken, $since, $until);
	        $this->syncOrganizationPageStatistics($accountId, $organizationId, $accessToken, $since, $until);
	        \Analytics::markSynced($accountId, 'linkedin', 'page');
	    }
	}

    protected function syncFollowerStatistics(int $accountId, string $organizationId, string $accessToken): void
	{
	    $url = 'https://api.linkedin.com/rest/organizationalEntityFollowerStatistics';

	    $response = Http::withHeaders([
	        'Authorization'     => 'Bearer ' . $accessToken,
	        'LinkedIn-Version'  => '202505',
	        'Content-Type'      => 'application/json',
	        'X-Restli-Protocol-Version' => '2.0.0',
	    ])->get($url, [
	        'q' => 'organizationalEntity',
	        'organizationalEntity' => "urn:li:organization:$organizationId"
	    ]);

	    if ($response->failed()) {
	        logger()->error('[LinkedInAnalytics] Failed to fetch follower statistics', [
	            'account_id' => $accountId,
	            'status'     => $response->status(),
	            'body'       => $response->body(),
	        ]);
	        return;
	    }

	    $data = $response->json();
	    $item = $data['elements'][0] ?? null;

	    if (!$item || empty($item['followerCountsByGeoCountry'])) {
	        logger()->warning('[LinkedInAnalytics] No follower data found.');
	        return;
	    }

	    // ===== TOTAL FOLLOWER COUNT =====
	    $totalFollowers = collect($item['followerCountsByGeoCountry'])
	        ->pluck('followerCounts')
	        ->map(fn($counts) => (int) ($counts['organicFollowerCount'] ?? 0) + (int) ($counts['paidFollowerCount'] ?? 0))
	        ->sum();

	    if ($totalFollowers > 0) {
	        SocialAnalytics::updateOrInsert([
	            'account_id'     => $accountId,
	            'social_network' => 'linkedin',
	            'metric'         => 'follower_count',
	            'date'           => now()->toDateString(),
	        ], [
	            'value'   => $totalFollowers,
	            'created' => time(),
	        ]);
	    }

	    // ===== COUNTRY DETAIL FOLLOWERS =====
	    $geoData = $item['followerCountsByGeoCountry'];
	    $geoIds = collect($geoData)->pluck('geo')->map(fn($urn) => (int) str_replace('urn:li:geo:', '', $urn));

	    // Request country names for geoIds
	    $geoResponse = Http::withHeaders([
	        'Authorization' => 'Bearer ' . $accessToken,
	        'LinkedIn-Version' => '202505',
	    ])->get('https://api.linkedin.com/v2/geo', [
	        'ids' => $geoIds->toArray()
	    ]);

	    $geoNames = collect($geoResponse->json()['results'] ?? [])->mapWithKeys(function ($item, $id) {
	        return [(string) $id => country_name_to_iso($item['defaultLocalizedName']['value']) ?? __('Unknown')];
	    });

	    foreach ($geoData as $geoItem) {
	        $geoUrn = $geoItem['geo'] ?? '';
	        $geoId = (int) str_replace('urn:li:geo:', '', $geoUrn);
	        $countryCode = $geoNames->get((string) $geoId, __('Unknown'));
	        $counts = $geoItem['followerCounts'] ?? [];

	        $value = (int) ($counts['organicFollowerCount'] ?? 0) + (int) ($counts['paidFollowerCount'] ?? 0);

	        if ($value > 0 && $countryCode !== __('Unknown')) {
	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'linkedin',
	                'metric'         => 'page_fans_country.' . $countryCode,
	                'date'           => now()->toDateString(),
	            ], [
	                'value'   => $value,
	                'created' => time(),
	            ]);
	        }
	    }
	}

	protected function syncOrganizationPageStatistics(int $accountId, string $organizationId, string $accessToken, string $since, string $until): void
	{
	    $startMs = Carbon::parse($since)->startOfDay()->timestamp * 1000;
	    $endMs = Carbon::parse($until)->endOfDay()->timestamp * 1000;

	    $response = Http::withHeaders([
	        'Authorization' => 'Bearer ' . $accessToken,
	        'LinkedIn-Version' => '202505',
	    ])->get('https://api.linkedin.com/rest/organizationPageStatistics', [
	        'q' => 'organization',
	        'organization' => "urn:li:organization:$organizationId",
	        'timeIntervals.timeRange.start' => $startMs,
	        'timeIntervals.timeRange.end' => $endMs,
	        'timeIntervals.timeGranularityType' => 'DAY',
	    ]);

	    if ($response->failed()) {
	        logger()->error('[LinkedIn] Failed to fetch organizationPageStatistics', [
	            'account_id' => $accountId,
	            'status' => $response->status(),
	            'body' => $response->body(),
	        ]);
	        return;
	    }

	    $data = $response->json();

	    foreach ($data['elements'] ?? [] as $element) {
	        $date = isset($element['timeRange']['start'])
	            ? Carbon::createFromTimestampMs($element['timeRange']['start'])->toDateString()
	            : now()->toDateString();

	        $views = $element['totalPageStatistics']['views'] ?? [];

	        foreach ($views as $viewType => $viewData) {
			    if (!isset($viewData['pageViews']) || $viewData['pageViews'] <= 0) continue;

			    $metric = 'page_view_' . str_replace('PageViews', '', \Str::snake($viewType));

			    SocialAnalytics::updateOrInsert([
			        'account_id'     => $accountId,
			        'social_network' => 'linkedin',
			        'metric'         => $metric,
			        'date'           => $date,
			    ], [
			        'value'   => (int) $viewData['pageViews'],
			        'created' => time(),
			    ]);
			}
	    }
	}

	protected function syncOrganizationPostSummary(int $accountId, string $organizationId, string $accessToken, string $since, string $until): void
	{
	    $startMs = Carbon::parse($since)->startOfDay()->timestamp * 1000;
	    $endMs   = Carbon::parse($until)->endOfDay()->timestamp * 1000;

	    $response = Http::withHeaders([
	        'Authorization' => 'Bearer ' . $accessToken,
	        'LinkedIn-Version' => '202505',
	        'Content-Type' => 'application/json',
	    ])->get('https://api.linkedin.com/rest/organizationalEntityShareStatistics', [
	        'q' => 'organizationalEntity',
	        'organizationalEntity' => "urn:li:organization:$organizationId",
	        'timeIntervals.timeGranularityType' => 'DAY',
	        'timeIntervals.timeRange.start' => $startMs,
	        'timeIntervals.timeRange.end' => $endMs,
	    ]);

	    if ($response->failed()) {
	        logger()->error('[LinkedIn] Failed to fetch share statistics', [
	            'account_id' => $accountId,
	            'status'     => $response->status(),
	            'body'       => $response->body(),
	        ]);
	        return;
	    }

	    $elements = $response->json()['elements'] ?? [];

	    if (empty($elements)) {
	        logger()->warning('[LinkedIn] No share statistics returned', [
	            'account_id' => $accountId,
	        ]);
	        return;
	    }

	    foreach ($elements as $element) {
	        $date = Carbon::createFromTimestampMs($element['timeRange']['start'] ?? 0)->toDateString();
	        $stats = $element['totalShareStatistics'] ?? [];

	        foreach ($stats as $key => $value) {
	            if (!is_numeric($value) || $value <= 0) continue;

	            $metric = \Str::snake($key);

	            SocialAnalytics::updateOrInsert([
	                'account_id'     => $accountId,
	                'social_network' => 'linkedin',
	                'metric'         => $metric,
	                'date'           => $date,
	            ], [
	                'value'   => (int) $value,
	                'created' => time(),
	            ]);
	        }
	    }
	}

	protected function syncPostsAndInsights(int $accountId, string $organizationId, string $accessToken): void
	{
	    $organizationUrn = 'urn:li:organization:' . $organizationId;

	    $response = Http::withHeaders([
	        'Authorization' => 'Bearer ' . $accessToken,
	        'LinkedIn-Version' => '202505',
	        'Content-Type' => 'application/json',
	        'X-Restli-Protocol-Version' => '2.0.0',
	    ])->get('https://api.linkedin.com/rest/posts', [
	        'q' => 'author',
	        'author' => $organizationUrn,
	        'sortBy' => 'LAST_MODIFIED',
	        'count' => 50,
	    ]);

	    if ($response->failed()) {
	        logger()->error('[LinkedIn] Failed to fetch posts', [
	            'account_id' => $accountId,
	            'status' => $response->status(),
	            'body' => $response->body(),
	        ]);
	        return;
	    }

	    $posts = $response->json()['elements'] ?? [];
		foreach ($posts as $post) {
		    $postUrn = $post['id'] ?? null;
		    if (!$postUrn) continue;

		    $createdTime = Carbon::createFromTimestampMs($post['createdAt']);
		    $createdAt = $createdTime->toDateTimeString();
		    $date = $createdTime->toDateString();

		    $postId = \Str::afterLast($post['id'], ':');
			$permalink = "https://www.linkedin.com/feed/update/urn:li:share:$postId";

			$thumbnail = null;

			SocialAnalyticsPost::updateOrInsert([
			    'account_id'     => $accountId,
			    'social_network' => 'linkedin',
			    'post_id'        => $post['id'],
			    'date'           => $date,
			], [
			    'message'        => $post['commentary'] ?? null,
			    'created_time'   => $createdAt,
			    'full_picture'   => $thumbnail,
			    'permalink_url'  => $permalink,
			    'type'           => 'post',
			    'status_type'    => $post['lifecycleState'] ?? null,
			    'details'        => json_encode(['source' => 'LinkedIn']),
			    'created'        => time(),
			]);
		}
	}

}
