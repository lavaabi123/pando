<?php

namespace Modules\AppPublishing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AppPublishing\Models\Posts;
use Modules\AppPublishing\Models\PostComment;
use Validator;
use Channels;
use Publishing;
use DB;
use Arr;
use Media;
use Auth;
use Modules\AppPublishing\Models\CalendarNote;
use Carbon\Carbon;

class AppPublishingController extends Controller
{
    public function index(Request $request)
    {

        $campaigns = DB::table("post_campaigns")->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})->get();
        $labels = DB::table("post_labels")->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})->get();

        return view(module("key") . '::index', [
            "campaigns" => $campaigns,
            "labels" => $labels,
        ]);
    }

    /**
     * Retrieve calendar events from the database with dynamic filters.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
	public function events(Request $request)
    {
        $teamId = $request->team_id;

        // Build the query from the Posts model
        $query = Posts::with('account');

        // Filter by team_id
        if ($teamId) {
            //$query->where('team_id', $teamId);
			$query->when(session('role_id') != 2, function($q) use ($teamId) {
				//return $q->where('team_id', $teamId);
			});
        }
		$query->where('brand_id', session('brand_id'));
        // Filter by date range (time_post)
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('time_post', [
                strtotime($request->start), 
                strtotime($request->end)
            ]);
        }

        //$query->where('status', '!=', 1);

        // Dynamic filter by status
        if ($request->filled('status') && $request->status !== '-1') {
            $query->where('status', $request->status);
        }

        // Dynamic filter by module_name (social network)
        if ($request->filled('module_name')) {
            $query->where('module', $request->module_name);
        }

        // Dynamic filter by campaign
        if ($request->filled('campaign')) {
            $query->where('campaign', $request->campaign);
        }

        // Dynamic filter by label
        if ($request->filled('label')) {
            $labels = is_array($request->label) ? $request->label : [$request->label];
            $query->where(function($q) use ($labels) {
                foreach ($labels as $label) {
                    $q->orWhereJsonContains('labels', (int)$label);
                }
            });
        }

        $query->orderBy('time_post', 'DESC');

        // Get the list of posts
        $posts = $query->get();
		$posts = $query->get()->groupBy('grouping_data')->map(function($group) {
			return $group->first(); // This gets the first item (most recent due to DESC order)
		})->values();

        // Transform posts into FullCalendar event objects.
        $events = $posts->map(function($post) {
            $postData = json_decode($post->data, true);
            $moduleInfo = \Module::find($post->module);

            $module = [];
            if ($moduleInfo) {
                $menu = $moduleInfo->get('menu');
                $module = [
                    'icon' => $menu['icon'],
                    'color' => $menu['color'],
                    'name' => $menu['name'],
                ];
            }

            switch ($post->type) {
                case 'text':  $type = 1; break;
                case 'link':  $type = 2; break;
                case 'media': $type = 3; break;
                default:      $type = 4; break;
            }

            $medias = $postData['medias'] ?? [];
            $media = !empty($medias) ? Media::url($medias[0]) : '';
$startTime = Carbon::createFromTimestamp($post->time_post)
                ->setTimezone(Auth::user()->timezone);
            return [
                'title'           => $postData['caption'] ?? 'No Title',
                'start'           => $startTime->toIso8601String(),
    'end'             => $startTime->toIso8601String(),
                'backgroundColor' => '000',
                'borderColor'     => '000',
                'textColor'       => $module['color'] ?? '',
                'className'       => '',
                'extendedProps'   => [
                    'id'           => $post->id_secure,
                    'grouping_data'=> $post->grouping_data,
                    'status'       => $post->status,
                    'type'         => $type,
                    'icon'         => $module['icon'] ?? '',
                    'color'        => $module['color'] ?? '',
                    'account_name' => $post->account->name ?? ($postData['account_name'] ?? ''),
                    'image'        => $media,
                    'caption'      => $postData['caption'] ?? '',
                    'link'      => $postData['link'] ?? '',
                    'time_post' => Carbon::createFromTimestamp($post->time_post)
                  ->setTimezone(Auth::user()->timezone)
                  ->format('h:i A'),
                    'module_name'  => $module['name'] ?? '',
                    'response'     => json_decode($post->result ?? '{}'),
                ],
            ];
        });

        // Return the events as JSON data with a 'data' key.
        return response()->json(['data' => $events]);
    }
	
	public function events_count(Request $request)
	{
		$teamId = $request->team_id;
		
		// Subquery to get the ID of the most recent post per grouping_data
		$subquery = Posts::select(DB::raw('MAX(id) as id'));
		
		if ($teamId) {
			$subquery->when(session('role_id') != 2, function($sq) use ($teamId) {
				//return $sq->where('team_id', $teamId);
			});
			//$subquery->where('team_id', $teamId);
		}
		$subquery->where('brand_id', session('brand_id'));
		
		if ($request->has('start') && $request->has('end')) {
			$subquery->whereBetween('time_post', [
				strtotime($request->start), 
				strtotime($request->end)
			]);
		}
		
		if ($request->filled('status') && $request->status !== '-1') {
			$subquery->where('status', $request->status);
		}
		
		if ($request->filled('module_name')) {
			$subquery->where('module', $request->module_name);
		}
		
		if ($request->filled('campaign')) {
			$subquery->where('campaign', $request->campaign);
		}
		
		if ($request->filled('label')) {
			$labels = is_array($request->label) ? $request->label : [$request->label];
			$subquery->where(function($q) use ($labels) {
				foreach ($labels as $label) {
					$q->orWhereJsonContains('labels', (int)$label);
				}
			});
		}
		
		$subquery->groupBy('grouping_data');
		
		// Main query to get full post data
		$query = Posts::with('account')
			->whereIn('id', $subquery)
			->orderBy('time_post', 'DESC');
		
		// Get posts
		$posts = $query->get();
		
		// Group by date
		$groupedByDate = $posts->groupBy(function($post) {
			return date('Y-m-d', $post->time_post);
		});
		
		// Transform to events
		$events = $groupedByDate->map(function($postsOnDate, $date) {
			$postCount = $postsOnDate->count();
			
			// Get comma-separated list of grouping_data
			$groupingDataList = $postsOnDate->pluck('grouping_data')
				->filter()
				->implode(',');
			
			return [
				'title'           => $postCount . ' post' . ($postCount > 1 ? 's' : ''),
				'start'           => $date,
				'backgroundColor' => '#e3f2fd',
				'borderColor'     => '#2196f3',
				'textColor'       => '#1976d2',
				'extendedProps'   => [
					'post_count'      => $postCount,
					'date'            => $date,
					'grouping_data'   => $groupingDataList,
				],
			];
		})->values();
		
		return response()->json(['data' => $events]);
	}
	
	public function alllist(Request $request, $type = 'all', $category = 'all', $date = null, $ids = null)
	{
		$date = $date ?? $request->input('date');
		$ids = $ids ?? $request->input('ids');
		$brandId = session('brand_id');
		$teamId = session('team_id');
		
		// Validate date
		$dateCheck = explode("-", $date);
		if (count($dateCheck) != 3 || !checkdate((int)$dateCheck[1], (int)$dateCheck[2], (int)$dateCheck[0])) {
			return response()->json(['status' => 0, 'message' => 'Invalid date']);
		}
		
		$dateStart = $date . " 00:00:00";
		$dateEnd = $date . " 23:59:59";
		
		// Status mapping
		$statusMap = [
			'published' => 3,
			'unpublished' => 4,
			'all' => [1, 3, 4],
			'default' => 1
		];
		
		$status = $statusMap[$type] ?? $statusMap['default'];
		
		// Build query - Remove posts.* and select specific columns
		$query = Posts::with('account')
    ->select([
        'posts.time_post',
        'posts.grouping_data',
        DB::raw('MAX(posts.repost_frequency) as repost_frequency'),
        DB::raw('MAX(posts.repost_until) as repost_until'),
        DB::raw('MAX(posts.team_id) as team_id'),
        DB::raw('MAX(posts.category) as category'),
        DB::raw('MAX(posts.type) as type'),
        DB::raw('MAX(posts.id) as id'),
        DB::raw('MAX(posts.id_secure) as id_secure'),
        DB::raw('MAX(posts.data) as data'),
        DB::raw('MAX(posts.status) as status'),
        DB::raw('MAX(posts.account_id) as account_id'),
        DB::raw('MAX(posts.brand_id) as brand_id'),
        DB::raw('MAX(accounts.name) as name'),
        DB::raw('MAX(accounts.username) as username'),
        DB::raw('FROM_UNIXTIME(posts.time_post, "%Y-%m-%d %H:%i:%s") as time_posts'),
        DB::raw('FROM_UNIXTIME(MAX(posts.repost_until), "%Y-%m-%d %H:%i:%s") as repost_untils'),
        DB::raw('GROUP_CONCAT(posts.social_network SEPARATOR ",") as social_networks'),
        DB::raw('GROUP_CONCAT(DISTINCT posts.social_network SEPARATOR ",") as social_network'),
        DB::raw('GROUP_CONCAT(accounts.avatar SEPARATOR ",") as avatars'),
        DB::raw('GROUP_CONCAT(accounts.url SEPARATOR ",") as urls'),
        DB::raw('GROUP_CONCAT(posts.result SEPARATOR ",") as results'),
        DB::raw('GROUP_CONCAT(posts.id SEPARATOR ",") as id_all'),
        DB::raw('GROUP_CONCAT(posts.id SEPARATOR ",") as ids'),
    ])
    ->join('accounts', 'posts.account_id', '=', 'accounts.id')
    //->where('posts.brand_id', $brandId)
    ->whereIn('posts.grouping_data', explode(',', $ids));

	// Category filter
	if ($category != 'all') {
		$query->where('posts.social_network', $category);
	}

	// Get filter parameters
	$fStatus = $request->input('f_status');
	$fAccountIds = $request->input('f_account_ids');

	// Apply filters
	if ($type == 'all' && empty($fStatus)) {
		$query->where(function($q) use ($dateStart, $dateEnd) {
			$q->where(function($subQ) use ($dateStart, $dateEnd) {
				$subQ->whereNotNull('posts.time_post')
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d %H:%i:%s') >= ?", [$dateStart])
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d %H:%i:%s') <= ?", [$dateEnd])
					 ->whereNull('posts.repost_until');
			})
			->orWhere(function($subQ) use ($dateStart, $dateEnd) {
				$subQ->whereNotNull('posts.time_post')
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d 00:00:00') <= ?", [$dateEnd])
					 ->whereRaw("FROM_UNIXTIME(posts.repost_until, '%Y-%m-%d 23:59:59') >= ?", [$dateStart]);
			});
		});
	} else if ($type == 'all' && (!empty($fStatus) || !empty($fAccountIds))) {
		if (!empty($fStatus)) {
			$statuses = array_map('intval', explode(',', urldecode($fStatus)));
			$query->whereIn('posts.status', $statuses);
		}
		
		if (!empty($fAccountIds)) {
			$accountIds = array_map('intval', explode(',', urldecode($fAccountIds)));
			$query->whereIn('posts.account_id', $accountIds);
		}
		
		$query->where(function($q) use ($dateStart, $dateEnd) {
			$q->where(function($subQ) use ($dateStart, $dateEnd) {
				$subQ->whereNotNull('posts.time_post')
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d %H:%i:%s') >= ?", [$dateStart])
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d %H:%i:%s') <= ?", [$dateEnd])
					 ->whereNull('posts.repost_until');
			})
			->orWhere(function($subQ) use ($dateStart, $dateEnd) {
				$subQ->whereNotNull('posts.time_post')
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d 00:00:00') <= ?", [$dateEnd])
					 ->whereRaw("FROM_UNIXTIME(posts.repost_until, '%Y-%m-%d 23:59:59') >= ?", [$dateStart]);
			});
		});
	} else {
		$query->where(function($q) use ($dateStart, $dateEnd, $status) {
			$q->where(function($subQ) use ($dateStart, $dateEnd, $status) {
				$subQ->whereNotNull('posts.time_post')
					 ->where('posts.status', $status)
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d %H:%i:%s') >= ?", [$dateStart])
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d %H:%i:%s') <= ?", [$dateEnd])
					 ->whereNull('posts.repost_until');
			})
			->orWhere(function($subQ) use ($dateStart, $dateEnd, $status) {
				$subQ->whereNotNull('posts.time_post')
					 ->where('posts.status', $status)
					 ->whereRaw("FROM_UNIXTIME(posts.time_post, '%Y-%m-%d 00:00:00') <= ?", [$dateEnd])
					 ->whereRaw("FROM_UNIXTIME(posts.repost_until, '%Y-%m-%d 23:59:59') >= ?", [$dateStart]);
			});
		});
	}

	// Group by ONLY time_post and grouping_data
	$query->groupBy('posts.time_post', 'posts.grouping_data')
		  ->orderBy('posts.time_post', 'ASC');

	$result = $query->get();
		//echo "<pre>";print_r($result);exit;
		// Add module info
		foreach ($result as $key => $post) {
			$moduleInfo = \Module::find($post->social_network . '_post');
			
			if ($moduleInfo) {
				$menu = $moduleInfo->get('menu');
				$post->module_name = $menu['name'] ?? '';
				$post->icon = $menu['icon'] ?? '';
				$post->color = $menu['color'] ?? '';
			} else {
				$post->module_name = '';
				$post->icon = '';
				$post->color = '';
			}
		}
		
		// Return view
		return view(module("key") . '::alllist', [
			'result' => $result,
			'date' => $date
		])->render();
	}
    public function preview(Request $request){
        $id = $request->id;

        $post = Posts::with('account')->where("grouping_data", $id)->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})->get();
        ms([
            "status" => 1,
            "data" => view(module("key") . '::preview', [
				"frame_posts" => $post,
                "post" => $post
            ])->render()
        ]);
    }
	
	public function preview_calendar(Request $request)
	{
		$id = $request->id;
		$teamId = $request->team_id;
		
		// Convert comma-separated string to array and filter empty values
		$groupingDataArray = array_filter(array_map('trim', explode(',', $id)));
		
		if (empty($groupingDataArray)) {
			return response()->json([
				"status" => 0,
				"message" => "Invalid grouping data"
			], 400);
		}
		
		// Get all posts with those grouping_data values
		$frame_posts = Posts::with('account')
			->whereIn('grouping_data', $groupingDataArray)
			->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $teamId);
			})
			->orderBy('time_post', 'DESC')
			->get()->unique('grouping_data');
		
		if ($frame_posts->isEmpty()) {
			return response()->json([
				"status" => 0,
				"message" => "No posts found"
			], 404);
		}
		
		return response()->json([
			"status" => 1,
			"data" => view(module("key") . '::preview', [
				"frame_posts" => $frame_posts
			])->render()
		]);
	}

	public function comments(Request $request){
        $id = $request->id;

        $comments = PostComment::where("grouping_data", $id)->get();
        ms([
            "status" => 1,
            "data" => view(module("key") . '::comments', [
                "comments" => $comments,
				"grouping_data" => $id
            ])->render()
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'comment' => 'required|string|max:500'
        ]);
		if(!empty($request->id)){
			$comment = PostComment::where("id", $request->id)->update([
				'comment' => $request->comment,
			]);

			return response()->json([
				'status' => 'success',
				'message' => 'Comment updated successfully',
				'comment' => [
					'id' => $request->id,
					'comment' => $request->comment,
				]
			]);
		}else{
			$comment = PostComment::create([
				'grouping_data' => $request->grouping_data,
				'user_id' => Auth::id(),
				'post_id' => 0,
				'comment' => $request->comment,
			]);

			return response()->json([
				'status' => 'success',
				'message' => 'Comment added successfully',
				'comment' => [
					'id' => $comment->id,
					'comment' => $comment->comment,
				]
			]);
		}
    }
	
	public function comments_destroy(Request $request)
    {
        $comment = PostComment::findOrFail($request->id);        
        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment deleted successfully'
        ]);
    }
	
    public function changePostDate(Request $request)
    {
        $newDate = $request->new_date;
        $id = $request->id;

        $post = Posts::where("id_secure", $id)->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})->first();
		
        if(!$post){
            ms([
                "status" => 0,
                "message" => __("The post does not exist or has been deleted.")
            ]);
        }

        $newTimePost = changeDateKeepTime($newDate, $post->time_post);

        Posts::where("id", $post->id)->update([
            "time_post" => $newTimePost,
            "changed" => time()
        ]);

        ms([ "status" => 1 ]);
    }
	
	public function composerget(Request $request)
    {
        $id = $request->input("id");
        $date = $request->input("date");
        $post = Posts::where("id_secure", $id)->first();
		$accountIds = !empty($post) ? Posts::where('grouping_data', $post->grouping_data)->pluck('account_id')->toArray() : [];
        $labels = DB::table('post_labels')
             ->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})
            ->where("status", 1)
            ->get();

        $campaigns = DB::table('post_campaigns')
             ->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})
            ->where("status", 1)
            ->get();

        return view(module("key") . '::composer', [
        "labels"    => $labels,
        "campaigns" => $campaigns,
        "post"      => $post,
        "date"      => $date,
		"method" => "get",
		"accountIds" => $accountIds
    ]);
    }
	
    public function composer(Request $request)
    {
        $id = $request->input("id");
        $date = $request->input("date");
        $post = Posts::where("id_secure", $id)->first();
        $accountIds = Posts::where('grouping_data', $post->grouping_data)->pluck('account_id')->toArray();
        $labels = DB::table('post_labels')
             ->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})
            ->where("status", 1)
            ->get();

        $campaigns = DB::table('post_campaigns')
             ->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})
            ->where("status", 1)
            ->get();

        return ms([
            "status" => 1,
            "data"   => view(module("key") . '::composer', [
                "labels"    => $labels,
                "campaigns" => $campaigns,
                "post"      => $post,
                "date"      => $date,
				"method" => "post",
				"accountIds" => $accountIds
            ])->render()
        ]);
    }

    public function getLinkInfo(Request $request){
        $url = $request->input('value');

        $linkInfo = getLinkInfo($url);

        return response()->json([
            "status" => 1,
            "data"   => $linkInfo
        ]);
    }

    public function save(Request $request)
    {
		
        $skipValidate    = (bool) $request->confirm;
        $type            = (string) $request->type;
        $postBy          = (int) $request->post_by;
        $caption         = (string) $request->caption;
        $timePosts       = (array) $request->time_posts;
        $link            = (string) $request->link;
        $medias          = (array) $request->medias;
        $options         = (array) $request->options;
        $campaignIdSecure = (string) $request->campaign;
        $labelIds        = (array) $request->labels;

        $currentTime     = time();
        $timePost        = (int) timestamp_sql($request->time_post);
        $intervalPerPost = $request->interval_per_post;
        $repostFrequency = $request->repost_frequency;
        $repostUntil     = isset($request->repost_until) ? timestamp_sql($request->repost_until) : null;
        $listData        = [];

        $quota = Publishing::checkQuota($request->team_id);
        if (!$quota['can_post']) {
            return ms([
                "status"  => 0,
                "message" => $quota['message']
            ]);
        }

        $channels = Channels::list($request->accounts);
        if (!$channels) {
            return ms([
                "status"  => 0,
                "message" => __("Please select at least one account")
            ]);
        }

        $rules = [
            'type'   => 'required|string',
        ];
        $messages = [
            'type.required' => __('Type is required'),
        ];

        switch ($type) {
            case "media":
                $rules['medias'] = 'required|array|min:1';
                $messages['medias.required'] = __('Please select at least one media');
                break;
            case "link":
                $rules['link'] = 'required|url';
                $messages['link.required'] = __('Link is required');
                $messages['link.url']      = __('Link must be a valid URL');
                break;
            default:
                $rules['caption'] = 'required|string';
                $messages['caption.required'] = __('Caption is required');
                $type = "text";
                break;
        }

        if ($postBy === 2) {
            $rules['time_post']         = 'required';
            $rules['repost_frequency']  = 'required';
            $rules['interval_per_post'] = 'required';
            $messages['time_post.required']         = __('Time post is required');
            $messages['repost_frequency.required']  = __('Repost frequency is required');
            $messages['interval_per_post.required'] = __('Interval per post is required');
        } elseif ($postBy === 3) {
            $rules['time_posts'] = 'required|array|min:1';
            $messages['time_posts.required'] = __('Please select at least a time post');
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ms([
                "status"  => 0,
                "message" => $validator->errors()->first()
            ]);
        }

        if ($postBy === 2) {
            if ($timePost <= $currentTime) {
                return ms([
                    "status"  => "error",
                    "message" => __("Time post must be greater than current time")
                ]);
            }

            if ($repostFrequency > 0) {
                if (!$repostUntil) {
                    return ms([
                        "status"  => "error",
                        "message" => __("Repost until is required")
                    ]);
                }
                if ($timePost > $repostUntil) {
                    return ms([
                        "status"  => "error",
                        "message" => __("Time post must be smaller than repost until")
                    ]);
                }
            }
        }

        $campaign = DB::table("post_campaigns")
            ->where(["id_secure" => $campaignIdSecure])
			->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})
            ->first();
        $campaignId = $campaign ? $campaign->id : 0;

        $labels = DB::table("post_labels")
            ->whereIn("id_secure", $labelIds)
            ->when(session('role_id') != 2, function($query) use ($request) {
				return $query->where('team_id', $request->team_id);
			})
            ->get();
        $labelIdsArray = $labels ? Arr::pluck($labels, 'id') : [];

        $postData = [
            "caption" => $caption,
            "link"    => $link,
            "medias"  => $medias,
            "options" => $options,
			"custom_thumbnail" => (string) $request->custom_thumbnail ?: null,
			"custom_thumbnail_index" => (int) $request->custom_thumbnail_index !== 0
                                        ? (int) $request->custom_thumbnail_index
                                        : ($request->custom_thumbnail ? 0 : -1),
        ];
        
        // Add TikTok settings if provided
        if ($request->has('tiktok_settings')) {
            $tiktokSettings = json_decode($request->tiktok_settings, true);
            if ($tiktokSettings) {
                $postData['tiktok_settings'] = $tiktokSettings;
            }
        }

        $data = [
            "campaign"         => $campaignId,
            "labels"           => $labelIdsArray,
            "team_id"          => $request->team_id,
            "function"         => "post",
            "type"             => $type,
            "data"             => json_encode($postData),
            "time_post"        => 0,
            "delay"            => $intervalPerPost,
            "repost_frequency" => $repostFrequency,
            "repost_until"     => ($repostFrequency == 0) ? null : $repostUntil,
            "result"           => "",
            "status"           => 3,
            "changed"          => $currentTime,
            "created"          => $currentTime,			
            "brand_id"         => session('brand_id'),
			"user_id"          => session('user_id'),
			"grouping_data"    => session('brand_id').''.session('user_id').''.time()
        ];

        if ($postBy === 2) {
            $data['time_post'] = $timePost;
        } elseif ($postBy === 3) {
            $timePosts = array_unique(array_filter($timePosts));
            $data['repost_frequency'] = 0;
            $data['repost_until']     = null;
            $data['delay']            = 0;
        } elseif ($postBy === 4) {
            $data['status']      = 1;
            $data['delay']       = 5;
            $data['time_post']   = null;
            $data['repost_until'] = null;
        } elseif ($postBy === 5) {
            $data['status']      = 2;
            $data['delay']       = 5;
            $data['time_post']   = $timePost;
            $data['repost_until'] = null;
        } else {
            $data['time_post'] = $currentTime;
        }

        foreach ($channels as $key => $channel) {
            $postId = $request->post_id ? $request->post_id : rand_string();
            $data['id_secure']      = $postId;
            $data['account_id']     = $channel->id;
            $data['social_network'] = $channel->social_network;
            $data['category']       = $channel->category;
            $data['api_type']       = $channel->login_type;
            $data['module']         = $channel->module;

            if ($postBy === 3) {
                foreach ($timePosts as $time) {
                    $data['time_post'] = (int)timestamp_sql($time);
                    $listData[] = (object)$data;
                }
            } elseif ($postBy === 2) {
                $data['time_post'] = $timePost + ($intervalPerPost * $key * 60);
                $listData[] = (object)$data;
            } else {
                $listData[] = (object)$data;
            }
        }

        $validatorResult = Publishing::validate($listData);
        $socialCanPost   = json_decode($validatorResult["can_post"]);

        if (($skipValidate && !empty($socialCanPost)) || $validatorResult["status"] == 1) {
            $result = Publishing::post($listData, $socialCanPost);
            return response()->json($result);
        }

        return response()->json($validatorResult);
    }

    public function destroyByFilter(Request $request)
    {
        $query = Posts::query();
        $query->where('team_id', $request->team_id);

        if ($request->filled('status') && $request->status !== '-1') {
            $query->where('status', $request->status);
        }

        if ($request->filled('module_name')) {
            $query->where('module', $request->module_name);
        }

        if ($request->filled('campaign')) {
            $query->where('campaign', $request->campaign);
        }

        if ($request->filled('label')) {
            $labels = is_array($request->label) ? $request->label : [$request->label];
            $query->where(function($q) use ($labels) {
                foreach ($labels as $label) {
                    $q->orWhereJsonContains('labels', (int)$label);
                }
            });
        }

        $postIds = $query->pluck('id')->toArray();

        $deleted = $query->delete();

        return response()->json([
            'status' => 1,
            'deleted' => $deleted,
            'post_ids' => $postIds,
            'message' => __("Deleted :count posts.", ['count' => $deleted])
        ]);
    }

    public function destroy(Request $request)
    {
        
		$groupingData = $request->input('id'); // Actually contains grouping_data value

		$postsToDelete = Posts::where('grouping_data', $groupingData)->get();

		if ($postsToDelete->isEmpty()) {
			return response()->json([
				'status' => 'error',
				'message' => 'No posts found'
			], 404);
		}

		// Delete all posts with this grouping_data
		Posts::where('grouping_data', $groupingData)->delete();

		return response()->json([
			'status' => 'success',
			'message' => 'Post deleted successfully'
		]);
		//$response = \DBHelper::destroy(Posts::class, $request->input('id'));
        //return response()->json($response);
    }
	
	public function move_to_queue(Request $request)
    {
		$postId = $request->input('id');
		if (!empty($postId)) {
			$id = explode(', ', $postId);
			if (count($id) == 1) {
				$post = Posts::where('grouping_data', $postId)->first();
				
				if ($post) {
					$res = Posts::selectRaw("GROUP_CONCAT(id SEPARATOR ', ') as ids")
						->where('grouping_data', $post->grouping_data)
						->groupBy('grouping_data')
						->first();
						
					$id = explode(', ', $res->ids);
				}
			}
		} else {
			$id = explode(', ', request()->input('id'));
		}
		
		$checkDate = 0;
		
		// Get post details
		$posts = Posts::whereIn('id', $id)->get();
		
		if ($posts->isNotEmpty()) {
			foreach ($posts as $post) {
				if (is_null($post->time_post) || $post->time_post <= time()) {
					$checkDate++;
				}
			}
		}
		
		if ($checkDate > 0) {
			return response()->json([
				'status' => 'error',
				'message' => __('Date is not Scheduled or Past date is set, Please assign date and approve.')
			]);
		}
		
		// Update all posts at once (more efficient)
		Posts::whereIn('id', $id)->update(['status' => 3]);
		
		return response()->json([
			'status' => 'success',
			'message' => __('Post Scheduled Successfully.')
		]);
	}
	
	public function addNote(Request $request)
    {
        $validated = $request->validate([
            'note_text' => 'required|string|max:1024',
            'note_date' => 'required|date',
        ]);

        CalendarNote::create([
            'notes' => $validated['note_text'],
            'date' => $validated['note_date'],
            'user_id' => Auth::id(),
            'brand_id' => session('brand_id'),
        ]);

        return response()->json(['message' => 'success']);
    }

    public function editNote(Request $request, $id)
    {
        $validated = $request->validate([
            'note_text' => 'required|string|max:1024',
            'note_date' => 'required|date',
        ]);

        $note = CalendarNote::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $note->update([
            'notes' => $validated['note_text'],
			'date' => $validated['note_date'],
        ]);

        return response()->json(['message' => 'success']);
    }

    public function getNote($date)
    {
        $notes = CalendarNote::with('user')
            ->where('date', $date)
            ->where('user_id', Auth::id())
            ->where('brand_id', session('brand_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        $notesHtml = '';
        
        if ($notes->isNotEmpty()) {
            foreach ($notes as $note) {
                $notesHtml .= view(module("key") . '::partials.calendar-note-item', [
                    'note' => $note
                ])->render();
            }
        }

        return response($notesHtml);
    }

    public function deleteNote($id)
    {
        CalendarNote::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return response('success');
    }
	
	public function getNotesForCalendar(Request $request)
	{
		$start = $request->input('start');
		$end = $request->input('end');
		
		if (!$start || !$end) {
			return response()->json(['data' => []]);
		}
		
		// Fetch notes within date range
		$notes = CalendarNote::with('user')
			->where('user_id', Auth::id())
			->where('brand_id', session('brand_id'))
			->whereBetween('date', [$start, $end])
			->orderBy('date', 'asc')
			->orderBy('created_at', 'desc')
			->get();
		
		// Group notes by date
		$groupedNotes = [];
		
		foreach ($notes as $note) {
			$dateKey = $note->date instanceof \Carbon\Carbon 
				? $note->date->format('Y-m-d') 
				: date('Y-m-d', strtotime($note->date));
			
			if (!isset($groupedNotes[$dateKey])) {
				$groupedNotes[$dateKey] = [
					'date' => $dateKey,
					'count' => 0,
					'notes' => []
				];
			}
			
			$groupedNotes[$dateKey]['count']++;
			$groupedNotes[$dateKey]['notes'][] = [
				'id' => $note->id,
				'text' => $note->notes,
				'user' => $note->user->name ?? 'Unknown',
				'created_at' => date('M d, Y H:i A', strtotime($note->created_at))
			];
		}
		
		return response()->json(['data' => $groupedNotes]);
	}
	
	
}
