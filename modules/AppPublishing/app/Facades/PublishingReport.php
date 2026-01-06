<?php

namespace Modules\AppPublishing\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\AppPublishing\Models\Posts;
use Modules\AppChannels\Models\Accounts;
use Modules\AppPublishing\Models\PostStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublishingReport extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'PublishingReport';
    }

    public static function postStatsByDay(Carbon $startDate, Carbon $endDate, $teamId = null)
	{
		$allDays = collect();
		$cur = $startDate->copy();
		while ($cur->lte($endDate)) {
			$allDays->push($cur->format('Y-m-d'));
			$cur->addDay();
		}
		
		$successQuery = PostStat::query()
			->selectRaw('FROM_UNIXTIME(created, "%Y-%m-%d") as date, COUNT(*) as total')
			->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
			->where('status', 4)->where('brand_id',session('brand_id'));
		if ($teamId) $successQuery->where('team_id', $teamId);
		$successData = $successQuery->groupBy('date')->pluck('total', 'date')->toArray();
		
		$failQuery = PostStat::query()
			->selectRaw('FROM_UNIXTIME(created, "%Y-%m-%d") as date, COUNT(*) as total')
			->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
			->where('status', 5)->where('brand_id',session('brand_id'));
		if ($teamId) $failQuery->where('team_id', $teamId);
		$failData = $failQuery->groupBy('date')->pluck('total', 'date')->toArray();
		
		$successValues = [];
		$failValues = [];
		$formattedDates = []; // Add this
		
		foreach ($allDays as $day) {
			$successValues[] = (int)($successData[$day] ?? 0);
			$failValues[] = (int)($failData[$day] ?? 0);
			
			// Format date as "01 Nov 25"
			$formattedDates[] = \Carbon\Carbon::parse($day)->format('d M y');
		}
		
		$successTotal = array_sum($successValues);
		$failTotal = array_sum($failValues);
		$total = $successTotal + $failTotal;
		$successRate = $total > 0 ? round($successTotal / $total * 100, 1) : 0;
		
		return [
			'categories' => $formattedDates, // Use formatted dates instead of $allDays->toArray()
			'series' => [
				['name' => __('Success'), 'data' => $successValues],
				['name' => __('Failed'),  'data' => $failValues],
			],
			'summary' => [
				'success_total' => $successTotal,
				'fail_total' => $failTotal,
				'total' => $total,
				'success_rate' => $successRate,
			]
		];
	}

    public static function postsByTeamForChart(Carbon $startDate, Carbon $endDate)
    {
        $data = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
			->where('brand_id',session('brand_id'))
            ->select('team_id', \DB::raw('COUNT(*) as total'))
            ->groupBy('team_id')
            // ->with('team') // Nếu có relation team
            ->get();

        $categories = [];
        $values = [];
        foreach ($data as $item) {
            $categories[] = $item->team->name ?? ('Team ' . $item->team_id);
            $values[] = (int)$item->total;
        }
        return [
            'categories' => $categories,
            'series' => [
                ['name' => __('Posts'), 'data' => $values]
            ]
        ];
    }

    public static function postsBySocialForChart(Carbon $startDate, Carbon $endDate)
    {
        $data = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
			->where('brand_id',session('brand_id'))
            ->select('social_network', \DB::raw('COUNT(*) as total'))
            ->groupBy('social_network')
            ->get();

        $seriesData = [];
        foreach ($data as $item) {
            $seriesData[] = ['name' => ucfirst($item->social_network), 'y' => (int)$item->total];
        }
        return [
            'series' => [[
                'name' => __('Posts'),
                'data' => $seriesData
            ]]
        ];
    }

    public static function statusMap($key = null)
    {
        $statuses = [
            1 => ['label' => __('Draft'),            'color' => '#f5c542'],
            2 => ['label' => __('Waiting Approve'),  'color' => '#4a90e2'],
            3 => ['label' => __('Processing'),       'color' => '#50e3c2'],
            4 => ['label' => __('Failed'),           'color' => '#ff4d4f'],
            5 => ['label' => __('Success'),          'color' => '#52c41a'],
            6 => ['label' => __('Stop/Pause'),       'color' => '#888888'],
        ];
        return $key ? ($statuses[$key] ?? ['label' => __('Unknown'), 'color' => '#bbb']) : $statuses;
    }

    public static function postInfo(Carbon $startDate, Carbon $endDate, $teamId = null)
    {
        $totalPosts = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
			->where('brand_id',session('brand_id'))
            ->count();

        $rangeDays = $startDate->diffInDays($endDate);
        $prevStart = $startDate->copy()->subDays($rangeDays);
        $prevEnd = $startDate;
        $prevTotal = Posts::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
			->where('brand_id',session('brand_id'))
            ->count();
        $totalGrowth = static::calcGrowth($prevTotal, $totalPosts);

        $statusMap = static::statusMap();
        $statusCounts = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
			->where('brand_id',session('brand_id'))
            ->select('status', \DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();

        $statusGrowth = [];
        foreach ($statusMap as $code => $info) {
            $current = (int)($statusCounts[$code] ?? 0);
            $prev = Posts::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('status', $code)
				->where('brand_id',session('brand_id'))
                ->count();
            $statusGrowth[$code] = static::calcGrowth($prev, $current);
        }

        $topAccount = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
			->where('brand_id',session('brand_id'))
            ->select('account_id', \DB::raw('COUNT(*) as total'))
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->with('account')
            ->first();

        $socialDistribution = Posts::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
			->where('brand_id',session('brand_id'))
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->select('social_network', \DB::raw('COUNT(*) as total'))
            ->groupBy('social_network')
            ->pluck('total', 'social_network')->toArray();

        return [
            'total_posts'         => $totalPosts,
            'total_growth'        => $totalGrowth,
            'status_map'          => $statusMap,
            'status_counts'       => $statusCounts,
            'status_growth'       => $statusGrowth,
            'top_account'         => $topAccount,
            'social_distribution' => $socialDistribution,
        ];
    }

    public static function postStatsGrowthInfo(Carbon $startDate, Carbon $endDate, $teamId = null)
    {
        $statusMap = [
            5 => ['label' => __('Failed'),  'color' => '#ff4d4f'],
            4 => ['label' => __('Success'), 'color' => '#52c41a'],
        ];

        $totalPosts = PostStat::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', [4, 5])
			->where('brand_id',session('brand_id'))
            ->count();

        $rangeDays = $startDate->diffInDays($endDate);
        $prevStart = $startDate->copy()->subDays($rangeDays);
        $prevEnd = $startDate;

        $prevTotal = PostStat::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', [4, 5])
			->where('brand_id',session('brand_id'))
            ->count();

        $totalGrowth = static::calcGrowth($prevTotal, $totalPosts);

        $statusCounts = PostStat::whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', [4, 5])
			->where('brand_id',session('brand_id'))
            ->select('status', \DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();

        $statusGrowth = [];
        foreach ([4, 5] as $code) {
            $current = (int)($statusCounts[$code] ?? 0);
            $prev = PostStat::whereBetween('created', [$prevStart->timestamp, $prevEnd->timestamp])
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('status', $code)
				->where('brand_id',session('brand_id'))
                ->count();
            $statusGrowth[$code] = static::calcGrowth($prev, $current);
        }

        return [
            'total_posts'   => $totalPosts,
            'total_growth'  => $totalGrowth,
            'status_map'    => $statusMap,
            'status_counts' => $statusCounts,
            'status_growth' => $statusGrowth,
        ];
    }

    public static function postsByStatusForChart(Carbon $startDate, Carbon $endDate, $teamId = null)
    {
        $statusMap = static::statusMap();

        $query = Posts::query()
            ->select('status', \DB::raw('COUNT(*) as total'))
            ->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
			->where('brand_id',session('brand_id'));
        if ($teamId) $query->where('team_id', $teamId);

        $statusData = $query->groupBy('status')->pluck('total', 'status')->toArray();

        $categories = [];
        $values = [];
        foreach ($statusMap as $code => $info) {
            $categories[] = $info['label'];
            $values[] = (int)($statusData[$code] ?? 0);
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
	public static function recentPostsStatus($limit = 10, $teamId = null)
	{
		// First, get the grouping_data with max created timestamp
		$subQuery = Posts::query()
			->select('grouping_data', DB::raw('MAX(created) as max_created'))
			->whereIn('status', [4, 5])
			->where('brand_id',session('brand_id'))
			->whereNotNull('grouping_data');
		
		if ($teamId) {
			$subQuery->where('team_id', $teamId);
		}
		
		$subQuery = $subQuery->groupBy('grouping_data')
			->orderBy('max_created', 'DESC')
			->limit($limit);
		
		// Get the grouping_data values
		$groupingDataList = $subQuery->pluck('grouping_data');
		
		if ($groupingDataList->isEmpty()) {
			return collect([]);
		}
		
		// Now get full details for these groups
		$query = Posts::query()
			->select([
				'posts.grouping_data',
				DB::raw('MAX(posts.id) as id'),
				DB::raw('MAX(posts.data) as data'),
				DB::raw('MAX(posts.type) as type'),
				DB::raw('MAX(posts.status) as status'),
				DB::raw('MAX(posts.result) as result'),
				DB::raw('MAX(posts.created) as created'),
				DB::raw('MAX(posts.time_post) as time_post'),
				DB::raw('GROUP_CONCAT(DISTINCT accounts.id ORDER BY accounts.id SEPARATOR ",") as account_ids'),
				DB::raw('GROUP_CONCAT(DISTINCT accounts.name ORDER BY accounts.id SEPARATOR "|||") as account_names'),
				DB::raw('GROUP_CONCAT(DISTINCT accounts.avatar ORDER BY accounts.id SEPARATOR "|||") as account_avatars'),
				DB::raw('GROUP_CONCAT(DISTINCT accounts.url ORDER BY accounts.id SEPARATOR "|||") as account_urls'),
				DB::raw('GROUP_CONCAT(DISTINCT posts.social_network ORDER BY posts.id SEPARATOR ",") as social_networks'),
			])
			->join('accounts', 'posts.account_id', '=', 'accounts.id')
			->whereIn('posts.grouping_data', $groupingDataList)
			->where('posts.brand_id',session('brand_id'))
			->groupBy('posts.grouping_data')
			->get();
		
		// Sort by created timestamp
		$posts = $query->sortByDesc('created')->values();
		
		$statusMap = static::statusMap();
		
		// Transform to collection of objects with proper properties
		$result = $posts->map(function($post) use ($statusMap) {
			// Decode data
			$dataArray = json_decode($post->data, true);
			
			// Parse grouped account data
			$accounts = [];
			if ($post->account_names) {
				$names = explode('|||', $post->account_names);
				$avatars = explode('|||', $post->account_avatars);
				$urls = explode('|||', $post->account_urls);
				$socialNetworks = explode(',', $post->social_networks);
				
				// Remove duplicates while maintaining order
				$uniqueAccounts = [];
				foreach ($names as $index => $name) {
					$key = $name . '|' . ($avatars[$index] ?? '');
					if (!isset($uniqueAccounts[$key])) {
						$network = $socialNetworks[$index] ?? 'unknown';
						
						// Get module info for icon and color
						$moduleInfo = \Module::find($network . '_post');
						$menu = $moduleInfo ? $moduleInfo->get('menu') : [];
						
						$uniqueAccounts[$key] = [
							'name' => $name,
							'avatar' => $avatars[$index] ?? '',
							'url' => $urls[$index] ?? '',
							'social_network' => $network,
							'icon' => $menu['icon'] ?? 'fa-brands fa-circle',
							'color' => $menu['color'] ?? '#999',
						];
					}
				}
				
				$accounts = array_values($uniqueAccounts);
			}
			
			// Return as stdClass object
			return (object) [
				'id' => $post->id,
				'grouping_data' => $post->grouping_data,
				'data' => $post->data,
				'type' => $post->type,
				'status' => $post->status,
				'result' => $post->result,
				'created' => $post->created,
				'time_post' => $post->time_post,
				'caption' => $dataArray['caption'] ?? '',
				'medias' => $dataArray['medias'] ?? [],
				'status_label' => $statusMap[$post->status]['label'] ?? $post->status,
				'status_color' => $statusMap[$post->status]['color'] ?? '#bbb',
				'accounts' => $accounts,
				'account_names' => $post->account_names,
				'account_avatars' => $post->account_avatars,
				'social_networks' => $post->social_networks,
			];
		});
		
		return $result;
	}

	public static function postsBySocialMedia(Carbon $startDate, Carbon $endDate, $teamId = null)
	{
		$query = PostStat::query()
			->selectRaw('social_network, COUNT(*) as total')
			->whereBetween('created', [$startDate->timestamp, $endDate->timestamp])
			->whereIn('status', [3, 4, 5])->where('brand_id',session('brand_id')); // Published, failed, or success
			
		
		if ($teamId) {
			$query->where('team_id', $teamId);
		}
		
		$data = $query->groupBy('social_network')
			->orderBy('total', 'DESC')
			->get();
		
		$socialMediaData = [];
		$colors = [
			'facebook' => '#1877f2',
			'instagram' => '#e4405f',
			'twitter' => '#000000',
			'linkedin' => '#0077b5',
			'pinterest' => '#bd081c',
			'google_business' => '#4285f4',
			'youtube' => '#ff0000',
			'tiktok' => '#000000',
		];
		
		foreach ($data as $item) {
			$network = strtolower($item->social_network);
			$socialMediaData[] = [
				'name' => ucfirst(str_replace('_', ' ', $network)),
				'y' => (int)$item->total,
				'color' => $colors[$network] ?? '#999999'
			];
		}
		
		return $socialMediaData;
	}
	
	public static function getDailyAlerts($teamId = null)
	{
		$brandId = session('brand_id');
		$today = now()->format('Y-m-d');
		$yesterday = now()->subDay()->format('Y-m-d');
		
		// Accounts with no scheduled posts today
		$noScheduledPosts = DB::table('accounts as a')
			->leftJoin(DB::raw("(
				SELECT COUNT(id) as post_count, account_id, social_network 
				FROM posts 
				WHERE brand_id = {$brandId}
				AND FROM_UNIXTIME(time_post, '%Y-%m-%d') = '{$today}'
				GROUP BY account_id, social_network
			) as b"), function($join) {
				$join->on('b.account_id', '=', 'a.id')
					 ->on('b.social_network', '=', 'a.social_network');
			})
			->select('a.id', 'a.social_network', 'a.name', 'a.avatar', 'b.post_count')
			->where('a.brand_id', $brandId)
			->whereNull('b.post_count')
			->get();
		
		// Accounts with inbox not cleared for more than 24 hours
		$yesterday = now()->subHours(24);

		// Get the oldest uncompleted message per account (more than 24 hours old)
		$inboxNotClearedIds = DB::table('inbox')
			->select('account_id', DB::raw('MIN(id) as min_id')) // Changed to MIN to get oldest
			->where('brand_id', $brandId)
			->where('created_time', '<', $yesterday) // Changed to < (messages OLDER than 24 hours)
			->where('is_completed', 0)
			->groupBy('account_id')
			->pluck('min_id');

		$inboxNotCleared = DB::table('inbox as i')
			->leftJoin('accounts as a', 'a.id', '=', 'i.account_id')
			->select('i.*', 'a.social_network', 'a.name', 'a.avatar')
			->whereIn('i.id', $inboxNotClearedIds)
			->orderByDesc('i.created_time')
			->get();
		// Posts pending approval
		$pendingApproval = DB::table('posts as p')
			->leftJoin('accounts as a', 'a.id', '=', 'p.account_id')
			->select('p.id', 'p.account_id', 'p.status', 'p.data', 'p.type', 'a.social_network', 'a.name', 'a.avatar')
			->where('p.brand_id', $brandId)
			->where('p.status', 2)
			->where('p.account_id', '>', 0)
			->get();
		
		return [
			'no_scheduled_posts' => $noScheduledPosts,
			'inbox_not_cleared' => $inboxNotCleared,
			'pending_approval' => $pendingApproval,
			'no_scheduled_posts_count' => count($noScheduledPosts),
			'inbox_not_cleared_count' => count($inboxNotCleared),
			'pending_approval_count' => count($pendingApproval),
		];
	}

	public static function getTodayCounts($dayType = 'daily', $teamId = null)
	{
		$brandId = session('brand_id');
		$today = now()->format('Y-m-d');
		
		$result = [
			'total_scheduled_post' => 0,
			'inbox_messages' => 0,
			'total_reviews' => 0,
			'new_people' => 0,
			'total_failed_post' => 0,
			'total_holidays' => 0,
			'date_range_text' => 'today',
			'day_type_text' => 'today',
		];
		
		if ($dayType === 'daily') {
			// Daily counts
			$result['total_scheduled_post'] = DB::table('posts')
				->where('brand_id', $brandId)
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') = ?", [$today])
				->count();
			
			$result['inbox_messages'] = DB::table('inbox')
				->where('brand_id', $brandId)
				->whereDate('created_time', $today)
				->where('is_completed', 0)
				->count();
			
			$result['total_failed_post'] = DB::table('posts')
				->where('brand_id', $brandId)
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') = ?", [$today])
				->where('status', 5)
				->count();
			
			$result['total_holidays'] = DB::table('calendar_holidays')
				->where('brand_id', $brandId)
				->where('date', $today)
				->count();
			
			$result['date_range_text'] = now()->format('F d, Y');
			$result['day_type_text'] = 'today';
			
		} elseif ($dayType === 'weekly') {
			// Weekly counts
			$startOfWeek = now()->startOfWeek()->format('Y-m-d');
			$endOfWeek = now()->format('Y-m-d');
			
			$result['total_scheduled_post'] = DB::table('posts')
				->where('brand_id', $brandId)
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') >= ?", [$startOfWeek])
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') <= ?", [$endOfWeek])
				->count();
			
			$result['inbox_messages'] = DB::table('inbox')
				->where('brand_id', $brandId)
				->where('created_time', '>=', $startOfWeek)
				->where('created_time', '<=', $endOfWeek)
				->where('is_completed', 0)
				->count();
			
			$result['total_failed_post'] = DB::table('posts')
				->where('brand_id', $brandId)
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') >= ?", [$startOfWeek])
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') <= ?", [$endOfWeek])
				->where('status', 5)
				->count();
			
			$result['total_holidays'] = DB::table('calendar_holidays')
				->where('brand_id', $brandId)
				->where('date', '>=', $startOfWeek)
				->where('date', '<=', $endOfWeek)
				->count();
			
			$result['date_range_text'] = now()->startOfWeek()->format('F d, Y') . ' - ' . now()->format('F d, Y');
			$result['day_type_text'] = 'this week';
			
		} elseif ($dayType === 'monthly') {
			// Monthly counts
			$startOfMonth = now()->startOfMonth()->format('Y-m-d');
			$today = now()->format('Y-m-d');
			
			$result['total_scheduled_post'] = DB::table('posts')
				->where('brand_id', $brandId)
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') >= ?", [$startOfMonth])
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') <= ?", [$today])
				->count();
			
			$result['inbox_messages'] = DB::table('inbox')
				->where('brand_id', $brandId)
				->where('created_time', '>=', $startOfMonth)
				->where('created_time', '<=', $today)
				->where('is_completed', 0)
				->count();
			
			$result['total_failed_post'] = DB::table('posts')
				->where('brand_id', $brandId)
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') >= ?", [$startOfMonth])
				->whereRaw("FROM_UNIXTIME(time_post, '%Y-%m-%d') <= ?", [$today])
				->where('status', 5)
				->count();
			
			$result['total_holidays'] = DB::table('calendar_holidays')
				->where('brand_id', $brandId)
				->where('date', '>=', $startOfMonth)
				->where('date', '<=', $today)
				->count();
			
			$result['date_range_text'] = now()->startOfMonth()->format('F d, Y') . ' - ' . now()->format('F d, Y');
			$result['day_type_text'] = 'this month';
		}
		
		// Total Reviews (from calendar_holidays - seems like placeholder in your code)
		$result['total_reviews'] = DB::table('reviews')
			->where('brand_id', $brandId)
			->where('date', $today)
			->count();
		
		// New People (from calendar_holidays - seems like placeholder in your code)
		$result['new_people'] = DB::table('people')
			->where('brand_id', $brandId)
			->whereDate('created_at', $today)
			->count();
		
		return $result;
	}

    public static function calcGrowth($previous, $current)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / max(1, $previous)) * 100, 1);
    }
}
