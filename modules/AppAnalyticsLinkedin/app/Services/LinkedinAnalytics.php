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
		$accounts = Accounts::where("brand_id", session('brand_id'))->where("social_network", "linkedin")->where('login_type', 1)->where("category", "page")->orderBy('id')->get();

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
		$hasCountryData = SocialAnalytics::where('account_id', $account->id)
        ->where('social_network', 'linkedin')
        ->where('metric', 'LIKE', 'page_fans_country.%')
        ->exists();
		// Get alternative demographics
		$followersByIndustry = $this->getFollowersByIndustry($account->id, $since, $until);
		$followersBySeniority = $this->getFollowersBySeniority($account->id, $since, $until);
		$followersByFunction = $this->getFollowersByFunction($account->id, $since, $until);
		$followersByCompanySize = $this->getFollowersByCompanySize($account->id, $since, $until);
		

        return [
            'status' => 'success',
            'account' => $account,
            'overview' => $overview,
			'hasCountryData' => $hasCountryData,
			'fansLocationMapChart' => $hasCountryData ? $this->getFansLocationMapChartData($account->id, $since, $until) : null,
			'topFansCountries' => $hasCountryData ? $this->getTopFansCountries($account->id, $since, $until) : null,
			'followersByIndustry' => $followersByIndustry,
			'followersBySeniority' => $followersBySeniority,
			'followersByFunction' => $followersByFunction,
			'followersByCompanySize' => $followersByCompanySize,
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
	
	public function getFollowersByIndustry(int $accountId, string $since, string $until): array
	{
		$data = DB::table('social_analytics')
			->where('account_id', $accountId)
			->where('social_network', 'linkedin')
			->where('metric', 'LIKE', 'followers_by_industry.%')
			->whereBetween('date', [$since, $until])
			->orderByDesc('value')
			->limit(10)
			->get();
		
		return $data->map(function($item) {
			$industryId = str_replace('followers_by_industry.', '', $item->metric);
			return [
				'industry' => $this->getIndustryName($industryId), // You'll need to map industry IDs
				'count' => $item->value,
			];
		})->toArray();
	}

	public function getFollowersBySeniority(int $accountId, string $since, string $until): array
	{
		$seniorityLabels = [
			'1' => 'Unpaid',
			'2' => 'Training',
			'3' => 'Entry',
			'4' => 'Senior',
			'5' => 'Manager',
			'6' => 'Director',
			'7' => 'VP',
			'8' => 'CXO',
			'9' => 'Owner',
			'10' => 'Partner',
		];
		
		$data = DB::table('social_analytics')
			->where('account_id', $accountId)
			->where('social_network', 'linkedin')
			->where('metric', 'LIKE', 'followers_by_seniority.%')
			->whereBetween('date', [$since, $until])
			->orderByDesc('value')
			->get();
		
		return $data->map(function($item) use ($seniorityLabels) {
			$seniorityId = str_replace('followers_by_seniority.', '', $item->metric);
			return [
				'seniority' => $seniorityLabels[$seniorityId] ?? "Level {$seniorityId}",
				'count' => $item->value,
			];
		})->toArray();
	}

	public function getFollowersByFunction(int $accountId, string $since, string $until): array
	{
		$functionLabels = [
			'1' => 'Accounting',
			'2' => 'Administrative',
			'3' => 'Arts and Design',
			'4' => 'Business Development',
			'5' => 'Community & Social Services',
			'6' => 'Consulting',
			'7' => 'Education',
			'8' => 'Engineering',
			'9' => 'Entrepreneurship',
			'10' => 'Finance',
			'11' => 'Healthcare Services',
			'12' => 'Human Resources',
			'13' => 'Information Technology',
			'14' => 'Legal',
			'15' => 'Marketing',
			'16' => 'Media & Communication',
			'17' => 'Military & Protective Services',
			'18' => 'Operations', // This is what you have (function:18)
			'19' => 'Product Management',
			'20' => 'Program & Project Management',
			'21' => 'Purchasing',
			'22' => 'Quality Assurance',
			'23' => 'Real Estate',
			'24' => 'Research',
			'25' => 'Sales',
			'26' => 'Support',
		];
		
		$data = DB::table('social_analytics')
			->where('account_id', $accountId)
			->where('social_network', 'linkedin')
			->where('metric', 'LIKE', 'followers_by_function.%')
			->whereBetween('date', [$since, $until])
			->orderByDesc('value')
			->get();
		
		return $data->map(function($item) use ($functionLabels) {
			$functionId = str_replace('followers_by_function.', '', $item->metric);
			return [
				'function' => $functionLabels[$functionId] ?? "Function {$functionId}",
				'count' => $item->value,
			];
		})->toArray();
	}

	public function getFollowersByCompanySize(int $accountId, string $since, string $until): array
	{
		$sizeLabels = [
			'size_self_employed' => 'Self-employed',
			'size_1_to_10' => '1-10 employees',
			'size_11_to_50' => '11-50 employees',
			'size_51_to_200' => '51-200 employees',
			'size_201_to_500' => '201-500 employees',
			'size_501_to_1000' => '501-1,000 employees',
			'size_1001_to_5000' => '1,001-5,000 employees',
			'size_5001_to_10000' => '5,001-10,000 employees',
			'size_10001_or_more' => '10,001+ employees',
		];
		
		$data = DB::table('social_analytics')
			->where('account_id', $accountId)
			->where('social_network', 'linkedin')
			->where('metric', 'LIKE', 'followers_by_company_size.%')
			->whereBetween('date', [$since, $until])
			->orderByDesc('value')
			->get();
		
		return $data->map(function($item) use ($sizeLabels) {
			$sizeKey = str_replace('followers_by_company_size.', '', $item->metric);
			return [
				'size' => $sizeLabels[$sizeKey] ?? $sizeKey,
				'count' => $item->value,
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
	    // Clean the org ID
		$orgId = str_replace('urn:li:organization:', '', $organizationId);
		
		// Use v2 API like CodeIgniter (not REST API)
		$url = 'https://api.linkedin.com/v2/organizationalEntityFollowerStatistics';
		
		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'X-Restli-Protocol-Version' => '2.0.0', // Keep this header
		])->get($url, [
			'q' => 'organizationalEntity',
			'organizationalEntity' => "urn:li:organization:{$orgId}",
			'oauth2_access_token' => $accessToken, // Token in query parameter, not header!
		]);

		if ($response->failed()) {
			logger()->error('[LinkedInAnalytics] Failed to fetch follower statistics', [
				'account_id' => $accountId,
				'status' => $response->status(),
				'body' => $response->body(),
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
			'Content-Type' => 'application/json',
		])->get('https://api.linkedin.com/v2/geo', [
			'ids' => 'List(' . implode(',', $geoIds->toArray()) . ')',
			'oauth2_access_token' => $accessToken,
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

	    // Use v2 API
		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
		])->get('https://api.linkedin.com/v2/organizationPageStatistics', [
			'q' => 'organization',
			'organization' => "urn:li:organization:$organizationId",
			'timeIntervals.timeRange.start' => $startMs,
			'timeIntervals.timeRange.end' => $endMs,
			'timeIntervals.timeGranularityType' => 'DAY',
			'oauth2_access_token' => $accessToken, // Token as query param
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
			'Content-Type' => 'application/json',
		])->get('https://api.linkedin.com/v2/organizationalEntityShareStatistics', [
			'q' => 'organizationalEntity',
			'organizationalEntity' => "urn:li:organization:$organizationId",
			'timeIntervals.timeGranularityType' => 'DAY',
			'timeIntervals.timeRange.start' => $startMs,
			'timeIntervals.timeRange.end' => $endMs,
			'oauth2_access_token' => $accessToken, // Token as query param
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
			'Content-Type' => 'application/json',
			'X-Restli-Protocol-Version' => '2.0.0',
		])->get('https://api.linkedin.com/v2/ugcPosts', [
			'q' => 'authors',
			'authors' => "List({$organizationUrn})",
			'sortBy' => 'CREATED',
			'count' => 50,
			'oauth2_access_token' => $accessToken, // Token as query param
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
	
	protected function getIndustryName(string $industryId): string
	{
		$industries = [
			'1' => 'Defense & Space',
			'2' => 'Computer Hardware',
			'3' => 'Computer Software',
			'4' => 'Computer Networking',
			'5' => 'Internet',
			'6' => 'Semiconductors',
			'7' => 'Telecommunications',
			'8' => 'Law Practice',
			'9' => 'Legal Services',
			'10' => 'Management Consulting',
			'11' => 'Biotechnology',
			'12' => 'Medical Practice',
			'13' => 'Hospital & Health Care',
			'14' => 'Pharmaceuticals',
			'15' => 'Veterinary',
			'16' => 'Medical Devices',
			'17' => 'Cosmetics',
			'18' => 'Apparel & Fashion',
			'19' => 'Sporting Goods',
			'20' => 'Tobacco',
			'21' => 'Supermarkets',
			'22' => 'Food Production',
			'23' => 'Consumer Electronics',
			'24' => 'Consumer Goods',
			'25' => 'Furniture',
			'26' => 'Retail',
			'27' => 'Entertainment',
			'28' => 'Gambling & Casinos',
			'29' => 'Leisure, Travel & Tourism',
			'30' => 'Hospitality',
			'31' => 'Restaurants',
			'32' => 'Sports',
			'33' => 'Food & Beverages',
			'34' => 'Motion Pictures and Film',
			'35' => 'Broadcast Media',
			'36' => 'Museums and Institutions',
			'37' => 'Fine Art',
			'38' => 'Performing Arts',
			'39' => 'Recreational Facilities and Services',
			'40' => 'Banking',
			'41' => 'Insurance',
			'42' => 'Financial Services',
			'43' => 'Real Estate',
			'44' => 'Investment Banking',
			'45' => 'Investment Management',
			'46' => 'Accounting',
			'47' => 'Construction',
			'48' => 'Building Materials',
			'49' => 'Architecture & Planning',
			'50' => 'Civil Engineering',
			'51' => 'Aviation & Aerospace',
			'52' => 'Automotive',
			'53' => 'Chemicals',
			'54' => 'Machinery',
			'55' => 'Mining & Metals',
			'56' => 'Oil & Energy',
			'57' => 'Shipbuilding',
			'58' => 'Utilities',
			'59' => 'Textiles',
			'60' => 'Paper & Forest Products',
			'61' => 'Railroad Manufacture',
			'62' => 'Farming',
			'63' => 'Ranching',
			'64' => 'Dairy',
			'65' => 'Fishery',
			'66' => 'Primary/Secondary Education',
			'67' => 'Higher Education',
			'68' => 'Education Management',
			'69' => 'Research',
			'70' => 'Military',
			'71' => 'Legislative Office',
			'72' => 'Judiciary',
			'73' => 'International Affairs',
			'74' => 'Government Administration',
			'75' => 'Executive Office',
			'76' => 'Law Enforcement',
			'77' => 'Public Safety',
			'78' => 'Public Policy',
			'79' => 'Marketing and Advertising',
			'80' => 'Newspapers',
			'81' => 'Publishing',
			'82' => 'Printing',
			'83' => 'Information Services',
			'84' => 'Libraries',
			'85' => 'Environmental Services',
			'86' => 'Package/Freight Delivery',
			'87' => 'Individual & Family Services',
			'88' => 'Religious Institutions',
			'89' => 'Civic & Social Organization',
			'90' => 'Consumer Services',
			'91' => 'Transportation/Trucking/Railroad',
			'92' => 'Warehousing',
			'93' => 'Airlines/Aviation',
			'94' => 'Maritime',
			'95' => 'Information Technology and Services',
			'96' => 'Market Research',
			'97' => 'Public Relations and Communications',
			'98' => 'Design',
			'99' => 'Nonprofit Organization Management',
			'100' => 'Fundraising',
			'101' => 'Program Development',
			'102' => 'Writing and Editing',
			'103' => 'Staffing and Recruiting',
			'104' => 'Professional Training & Coaching',
			'105' => 'Venture Capital & Private Equity',
			'106' => 'Political Organization',
			'107' => 'Translation and Localization',
			'108' => 'Computer Games',
			'109' => 'Events Services',
			'110' => 'Arts and Crafts',
			'111' => 'Electrical/Electronic Manufacturing',
			'112' => 'Online Media',
			'113' => 'Nanotechnology',
			'114' => 'Music',
			'115' => 'Logistics and Supply Chain',
			'116' => 'Plastics',
			'117' => 'Computer & Network Security',
			'118' => 'Wireless',
			'119' => 'Alternative Dispute Resolution',
			'120' => 'Security and Investigations',
			'121' => 'Facilities Services',
			'122' => 'Outsourcing/Offshoring',
			'123' => 'Health, Wellness and Fitness',
			'124' => 'Alternative Medicine',
			'125' => 'Media Production',
			'126' => 'Animation',
			'127' => 'Commercial Real Estate',
			'128' => 'Capital Markets',
			'129' => 'Think Tanks',
			'130' => 'Philanthropy',
			'131' => 'E-Learning',
			'132' => 'Wholesale',
			'133' => 'Import and Export',
			'134' => 'Mechanical or Industrial Engineering',
			'135' => 'Photography',
			'136' => 'Human Resources',
			'137' => 'Business Supplies and Equipment',
			'138' => 'Mental Health Care',
			'139' => 'Graphic Design',
			'140' => 'International Trade and Development',
			'141' => 'Wine and Spirits',
			'142' => 'Luxury Goods & Jewelry',
			'143' => 'Renewables & Environment',
			'144' => 'Glass, Ceramics & Concrete',
			'145' => 'Packaging and Containers',
			'146' => 'Industrial Automation',
			'147' => 'Government Relations',
		];

		return $industries[$industryId] ?? "Industry {$industryId}";
	}

	/**
	 * Get function name from LinkedIn function ID
	 */
	protected function getFunctionName(string $functionId): string
	{
		$functions = [
			'1' => 'Accounting',
			'2' => 'Administrative',
			'3' => 'Arts and Design',
			'4' => 'Business Development',
			'5' => 'Community & Social Services',
			'6' => 'Consulting',
			'7' => 'Education',
			'8' => 'Engineering',
			'9' => 'Entrepreneurship',
			'10' => 'Finance',
			'11' => 'Healthcare Services',
			'12' => 'Human Resources',
			'13' => 'Information Technology',
			'14' => 'Legal',
			'15' => 'Marketing',
			'16' => 'Media & Communication',
			'17' => 'Military & Protective Services',
			'18' => 'Operations',
			'19' => 'Product Management',
			'20' => 'Program & Project Management',
			'21' => 'Purchasing',
			'22' => 'Quality Assurance',
			'23' => 'Real Estate',
			'24' => 'Research',
			'25' => 'Sales',
			'26' => 'Support',
		];

		return $functions[$functionId] ?? "Function {$functionId}";
	}

	/**
	 * Get seniority name from LinkedIn seniority ID
	 */
	protected function getSeniorityName(string $seniorityId): string
	{
		$seniorities = [
			'1' => 'Unpaid',
			'2' => 'Training',
			'3' => 'Entry Level',
			'4' => 'Senior',
			'5' => 'Manager',
			'6' => 'Director',
			'7' => 'VP',
			'8' => 'CXO',
			'9' => 'Owner',
			'10' => 'Partner',
		];

		return $seniorities[$seniorityId] ?? "Level {$seniorityId}";
	}

	/**
	 * Get company size label from staff count range code
	 */
	protected function getCompanySizeLabel(string $staffCountRange): string
	{
		$sizes = [
			'SIZE_SELF_EMPLOYED' => 'Self-employed',
			'SIZE_1' => '1 employee',
			'SIZE_2_TO_10' => '2-10 employees',
			'SIZE_11_TO_50' => '11-50 employees',
			'SIZE_51_TO_200' => '51-200 employees',
			'SIZE_201_TO_500' => '201-500 employees',
			'SIZE_501_TO_1000' => '501-1,000 employees',
			'SIZE_1001_TO_5000' => '1,001-5,000 employees',
			'SIZE_5001_TO_10000' => '5,001-10,000 employees',
			'SIZE_10001_OR_MORE' => '10,001+ employees',
		];

		return $sizes[$staffCountRange] ?? str_replace('_', ' ', ucwords(strtolower($staffCountRange)));
	}

}
