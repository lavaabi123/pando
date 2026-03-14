<?php

namespace Modules\AppInbox\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\AppInbox\Models\Inbox;
use Modules\AppInbox\Models\InboxComment;
use Modules\AppInbox\Models\InboxTag;
use Modules\AppInbox\Models\InboxTagManage;
use Modules\AppInbox\Models\InboxUserManage;
use Modules\AppChannels\Models\Accounts;

class InboxController extends Controller
{
    protected $perPage = 4;

    public function __construct()
    {
        // Add middleware if needed
    }

    /**
     * Display inbox index page
     */
    public function index(Request $request)
    {
        $teamId = $request->team_id;
        $brandId = session('brand_id');

        // Get brand detail
        $brandDetail = DB::table('brands')->where('id', $brandId)->first();
        $brandDetailData = [];
        
        if (!empty($brandDetail->data) && $brandDetail->data != 'null') {
            $brandDetailData = json_decode($brandDetail->data, true);
        }

        // Get accounts (you'll need to implement this based on your account model)
        $accounts = $this->getAccountsByBrand($brandDetailData);
        // Get lists
        $inboxList = $this->getInboxList();
        $brandsList = $this->getBrandsList();
        $usersList = $this->getUsersList();
        $tagsList = $this->getTagsList();

        return view('appinbox::index', [
            'title' => 'Inbox',
            'inbox_list' => $inboxList,
            'brands_list' => $brandsList,
            'accounts' => $accounts,
            'users_list' => $usersList,
            'tags_list' => $tagsList,
            'module' => $request->module,
        ]);
    }

    public function ajaxList(Request $request)
{
    $wheres = [];
    $whereIn = [];
    $filterText = '';

    $wheres['to_type'] = 'me';
    $wheres['is_deleted'] = 0;

    $itemFilter = $request->input('itemFilter');
    if (!empty($itemFilter)) {
        if ($itemFilter == 'Inbox') {
            $whereIn['is_completed'] = [0, 1];
            $whereIn['is_sent'] = [0, 1];
        } elseif ($itemFilter == 'Completed') {
            $wheres['is_completed'] = 1;
        } elseif ($itemFilter == 'Pending') {
            $wheres['is_completed'] = 0;
            $wheres['is_sent'] = 0;
        }
    } else {
        $wheres['is_completed'] = 0;
        $wheres['is_sent'] = 0;
    }

    if ($request->has('brand') && !empty($request->brand)) {
        $whereIn['brand_id'] = $request->brand;
        foreach ($request->brand as $brand) {
            $brandName = $this->getBrandName($brand);
            $filterText .= $this->makeChip('Brand', '<i class="fa-light fa-tag"></i>', null, $brandName, "close_filter('brand[]',$brand)");
        }
    } else {
        $wheres['brand_id'] = session('brand_id');
    }

    if ($request->has('users') && !empty($request->users)) {
        $whereIn['u2.id'] = $request->users;
        foreach ($request->users as $userId) {
            $userName = $this->getUserName($userId);
            $filterText .= $this->makeChip('User', '<i class="fa-light fa-user"></i>', null, $userName, "close_filter('users[]',$userId)");
        }
    }

    if ($request->has('tags') && !empty($request->tags)) {
        if (in_array(0, $request->tags)) {
            $wheres['t.tag_ids'] = null;
        } else {
            $whereIn['t2.id'] = $request->tags;
        }
        foreach ($request->tags as $tag) {
            if ($tag == 0) {
                $filterText .= $this->makeChip('Tag', '<i class="fa-light fa-hashtag"></i>', null, 'Untag items', "close_filter('tags[]',0)");
            } else {
                $tagName = $this->getTagName($tag);
                $filterText .= $this->makeChip('Tag', '<i class="fa-light fa-hashtag"></i>', null, $tagName, "close_filter('tags[]',$tag)");
            }
        }
    }

    if ($request->has('accounts') && !empty($request->accounts)) {
        $whereIn['account_id'] = $request->accounts;
        foreach ($request->accounts as $account) {
            $profile = $this->getProfileName($account);
            $icon    = get_social_media_icon($profile->social_network);
            $avatar  = '<img src="' . asset('storage/app/public/' . $profile->avatar) . '" style="width:16px;height:16px;border-radius:50%;object-fit:cover;border:1px solid #e0e0e0;flex-shrink:0;" alt="">';
            $filterText .= $this->makeChip('Profile', $icon, $avatar, $profile->name, "close_filter('accounts[]',$account)");
        }
    }

    if ($request->has('eventType') && !empty($request->eventType)) {
        $events = [];
        $networkType = [];
        foreach ($request->eventType as $et) {
            $parts = explode('_', $et);
            $events[]      = $parts[1] ?? '';
            $networkType[] = $parts[0] ?? '';
            $icon = get_social_media_icon($parts[0] ?? '');
            $filterText .= $this->makeChip('Type', $icon, null, $parts[1] ?? '', "close_filter('eventType[]','$et')");
        }
        $whereIn['media_type'] = $networkType;
        $whereIn['inbox_type'] = $events;
    }

    if ($request->has('dateRange') && !empty($request->dateRange)) {
        $dateRange = explode(',', $request->dateRange);
        if (count($dateRange) == 2) {
            $wheres['date_start'] = date('Y-m-d 00:00:00', strtotime($dateRange[0]));
            $wheres['date_end']   = date('Y-m-d 23:59:59', strtotime($dateRange[1]));
            $filterText .= $this->makeChip('Date', '<i class="fa-light fa-calendar"></i>', null, $request->dateRange, "close_filter('dateRange','" . $request->dateRange . "')");
        }
    }

    if ($request->has('itemFav') && !empty($request->itemFav)) {
        if ($request->itemFav == 'Favourite') {
            $wheres['is_favourite'] = 1;
        }
    }

    // Get inbox data
    $inboxData      = $this->getInboxData($wheres, $whereIn);
    $inboxComments  = $this->getInboxCommentsData($wheres, $whereIn);

    $inboxArray     = json_decode(json_encode($inboxData), true);
    $commentsArray  = json_decode(json_encode($inboxComments), true);

    $inboxList = array_merge($inboxArray, $commentsArray);
    usort($inboxList, function ($a, $b) {
        return strtotime($b['created_time']) - strtotime($a['created_time']);
    });

    $page         = $request->input('page', 1);
    $totalRecords = count($inboxList);
    $totalPages   = ceil($totalRecords / $this->perPage);
    $offset       = ($page - 1) * $this->perPage;
    $inboxList    = array_slice($inboxList, $offset, $this->perPage);

    $pagerContainer = $this->generatePagination($page, $totalPages);

    $detailView = '';
    if (!empty($inboxList)) {
        $detailView = $this->getDetailView($inboxList[0], $wheres, $whereIn);
    }

    return response()->json([
        'filter_text' => $filterText,
        'list' => view('appinbox::ajax_list', [
            'inbox_list'     => $inboxList,
            'page'           => $page,
            'pagerContainer' => $pagerContainer
        ])->render(),
        'list_detail' => $detailView
    ]);
}

private function makeChip(string $type, string $icon, ?string $avatar, string $label, string $onclose): string
{
    $avatarHtml = $avatar
        ? '<span style="display:inline-flex;align-items:center;margin-right:2px;">' . $avatar . '</span>'
        : '';

    return '
    <li style="list-style:none;display:inline-block;margin:3px 4px 3px 0;">
        <div style="display:inline-flex;align-items:center;gap:5px;background:#fff;border:1px solid #c8e6d6;border-radius:20px;padding:4px 7px 4px 10px;font-size:12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);max-width:220px;">
            <span style="display:inline-flex;align-items:center;font-size:13px;flex-shrink:0;">' . $icon . '</span>
            <span style="font-size:10px;font-weight:700;color:#2e7d52;text-transform:uppercase;letter-spacing:0.4px;white-space:nowrap;">' . $type . '</span>
            <span style="width:1px;height:12px;background:#d1e8db;display:inline-block;flex-shrink:0;"></span>
            ' . $avatarHtml . '
            <span style="font-size:12px;font-weight:500;color:#333;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100px;" title="' . htmlspecialchars($label) . '">' . htmlspecialchars($label) . '</span>
            <span onclick="' . $onclose . '"
                  style="display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;background:#e8f5ee;color:#666;cursor:pointer;font-size:8px;padding:0;margin-left:2px;flex-shrink:0;"
                  onmouseover="this.style.background=\'#fde8e8\';this.style.color=\'#e53935\';"
                  onmouseout="this.style.background=\'#e8f5ee\';this.style.color=\'#666\';">
                <i class="fa fa-times"></i>
            </span>
        </div>
    </li>';
}
	/**
     * Get detail view for AJAX
     */
    public function ajaxListDetail(Request $request)
    {
        $wheres = [];
        $whereIn = [];
		if (empty($request->conversation_id)) {
            $item = InboxComment::where('id', $request->id)->get()->toArray();
        } else {
            $item = Inbox::where('id', $request->id)->get()->toArray();
        }
		
		if (empty($request->conversation_id)) {
            $detailView = $this->getCommentDetailView($item[0], $wheres, $whereIn);
        } else {
            $detailView = $this->getMessageDetailView($item[0], $wheres, $whereIn);
        }
		return response()->json([
            'list_detail' => $detailView
        ]);
	}

    /**
     * Save comment/message
     */
    public function saveComment(Request $request)
    {
        $id = $request->detail_id;
        $comment = $request->comment;
        $conversationId = $request->conversation_id;
        $completeId = $request->complete_id ?? '';

        if ($request->inbox_type == 'comment') {
            // It's a comment
            $result = $this->postComment($id, $comment, $conversationId, $completeId);
        } else if ($request->inbox_type == 'message' || $request->inbox_type == 'Messenger'){
            // It's a message
            $result = $this->postMessage($id, $comment, $conversationId, $completeId);
        }else if ($request->inbox_type == 'tag'){
            // It's a Tag
            $result = $this->postMessage($id, $comment, $conversationId, $completeId);
        }else if ($request->inbox_type == 'mention'){
            // It's a Mention
            $result = $this->postMessage($id, $comment, $conversationId, $completeId);
        }else if ($request->inbox_type == 'reviews'){
            // It's a Review
            $result = $this->postMessage($id, $comment, $conversationId, $completeId);
        }

        return response()->json($result);
    }
	
    /**
     * Delete single message
     */
    public function deleteMessage(Request $request)
    {
        $id = $request->id;
        $table = $request->table;

        DB::table($table)->where('id', $id)->update(['is_deleted' => 1]);

        return response()->json(['message' => 'success']);
    }

    /**
     * Delete messages in bulk
     */
    public function deleteMessageBulk(Request $request)
    {
        $inboxComments = [];
        $inbox = [];
        $ids = $request->ids;

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $parts = explode('--', $id);
                if ($parts[0] == 'inbox') {
                    $inbox[] = $parts[1];
                } elseif ($parts[0] == 'inbox_comments') {
                    $inboxComments[] = $parts[1];
                }
            }
        }

        if (!empty($inbox)) {
            DB::table('inbox')->whereIn('id', $inbox)->update(['is_deleted' => 1]);
        }

        if (!empty($inboxComments)) {
            DB::table('inbox_comments')->whereIn('id', $inboxComments)->update(['is_deleted' => 1]);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Mark selected posts as complete
     */
    public function makePostCompleteSelected(Request $request)
    {
        $inboxComments = [];
        $inbox = [];
        $ids = $request->ids;

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $parts = explode('--', $id);
                if ($parts[0] == 'inbox') {
                    $inbox[] = $parts[1];
                } elseif ($parts[0] == 'inbox_comments') {
                    $inboxComments[] = $parts[1];
                }
            }
        }

        if (!empty($inbox)) {
            DB::table('inbox')->whereIn('id', $inbox)->update(['is_completed' => 1]);
        }

        if (!empty($inboxComments)) {
            DB::table('inbox_comments')->whereIn('id', $inboxComments)->update(['is_completed' => 1]);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Mark all posts as complete
     */
    public function makePostCompleteAll(Request $request)
    {
        DB::table('inbox')
            ->where('to_type', 'me')
            ->where('is_deleted', 0)
            ->where('is_completed', 0)
            ->where('is_sent', 0)
            ->where('brand_id', session('brand_id'))
            ->update(['is_completed' => 1]);

        DB::table('inbox_comments')
            ->where('to_type', 'me')
            ->where('is_deleted', 0)
            ->where('is_completed', 0)
            ->where('is_sent', 0)
            ->where('brand_id', session('brand_id'))
            ->update(['is_completed' => 1]);

        return response()->json(['message' => 'success']);
    }

    /**
     * Mark selected posts as incomplete
     */
    public function makePostIncompleteSelected(Request $request)
    {
        $inboxComments = [];
        $inbox = [];
        $ids = $request->ids;

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $parts = explode('--', $id);
                if ($parts[0] == 'inbox') {
                    $inbox[] = $parts[1];
                } elseif ($parts[0] == 'inbox_comments') {
                    $inboxComments[] = $parts[1];
                }
            }
        }

        if (!empty($inbox)) {
            DB::table('inbox')->whereIn('id', $inbox)->update(['is_completed' => 0]);
        }

        if (!empty($inboxComments)) {
            DB::table('inbox_comments')->whereIn('id', $inboxComments)->update(['is_completed' => 0]);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Mark all posts as incomplete
     */
    public function makePostIncompleteAll(Request $request)
    {
        DB::table('inbox')
            ->where('to_type', 'me')
            ->where('is_deleted', 0)
            ->where('is_completed', 1)
            ->where('is_sent', 0)
            ->where('brand_id', session('brand_id'))
            ->update(['is_completed' => 0]);

        DB::table('inbox_comments')
            ->where('to_type', 'me')
            ->where('is_deleted', 0)
            ->where('is_completed', 1)
            ->where('is_sent', 0)
            ->where('brand_id', session('brand_id'))
            ->update(['is_completed' => 0]);

        return response()->json(['message' => 'success']);
    }

    /**
     * Mark post as complete
     */
    public function makePostComplete(Request $request)
    {
        $id = $request->conversation_id;
        
        if (empty($id)) {
            DB::table('inbox_comments')
                ->where('id', $request->inbox_id)
                ->update(['is_completed' => 1]);
        } else {
            DB::table('inbox')
                ->where('id', $request->inbox_id)
                ->update(['is_completed' => 1]);
        }

        $inboxCount = session('brand_id') ? $this->getInboxCount(session('brand_id')) : 0;

        return response()->json([
            'message' => 'success',
            'inbox_count' => $inboxCount
        ]);
    }

    /**
     * Mark post as uncomplete
     */
    public function makePostUncomplete(Request $request)
    {
        DB::table('inbox')
            ->where('id', $request->inbox_id)
            ->update(['is_completed' => 0]);

        $inboxCount = session('brand_id') ? $this->getInboxCount(session('brand_id')) : 0;

        return response()->json([
            'message' => 'success',
            'inbox_count' => $inboxCount
        ]);
    }

    /**
     * Add new tag
     */
    public function addTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tag_name' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        $tag = InboxTag::create([
            'tag_name' => $request->tag_name,
            'added_user_id' => session('user_id'),
            'brand_id' => session('brand_id'),
        ]);

        $html = '<li class="py-1 d-flex">
                    <input type="checkbox" name="tags[]" value="' . $tag->id . '" class="me-2">
                    <label class="form-check-label" for="Inbox">' . $request->tag_name . '</label>
                </li>';

        return response()->json([
            'message' => 'success',
            'html' => $html
        ]);
    }

    /**
     * Assign tag to inbox item
     */
	public function assignTag(Request $request)
	{
		$tagIds = !empty($request->selected_tags) ? implode(',', $request->selected_tags) : '';
		
		if (empty($tagIds)) {
			DB::table('inbox_tags_manage')
				->where('inbox_id', $request->inbox_id)
				->where('table_name', $request->table)
				->delete();
			
			return response()->json([
				'message' => 'success',
				'html' => '',
				'ids' => ''
			]);
		}
		
		DB::statement("
			INSERT INTO inbox_tags_manage (inbox_id, tag_ids, table_name, added_user_id, brand_id) 
			VALUES (?, ?, ?, ?, ?) 
			ON DUPLICATE KEY UPDATE 
				tag_ids = ?,
				added_user_id = ?",
			[
				$request->inbox_id,
				$tagIds,
				$request->table,
				session('user_id'),
				session('brand_id'),
				$tagIds,
				session('user_id')
			]
		);
		
		$html = '';
		if (!empty($tagIds)) {
			$tagIdArray = array_filter(explode(',', $tagIds));
			
			if (!empty($tagIdArray)) {
				$tagNames = DB::table('inbox_tags')
					->whereIn('id', $tagIdArray)
					->pluck('tag_name');
				
				foreach ($tagNames as $tagName) {
					$html .= '<span class="badge bg-secondary me-2">'
						   . '<i class="fa-tag fal me-1"></i>'
						   . '<span>' . e($tagName) . '</span>'
						   . '</span>';
				}
			}
		}
		
		return response()->json([
			'message' => 'success',
			'html' => $html,
			'ids' => $tagIds
		]);
	}

    /**
     * Set favourite status
     */
    public function setFavourite(Request $request)
    {
        DB::table($request->table)
            ->where('id', $request->inbox_id)
            ->update(['is_favourite' => $request->fav]);

        return response()->json(['message' => 'success']);
    }

    /**
     * Assign user to inbox item
     */
    public function assignUser(Request $request)
	{
		// Get selected user IDs (could be empty if all unchecked)
		$userIds = !empty($request->selected_users) ? implode(',', $request->selected_users) : '';
		
		// If no users selected, delete the entire record
		if (empty($userIds)) {
			DB::table('inbox_users_manage')
				->where('inbox_id', $request->inbox_id)
				->where('table_name', $request->table)
				->delete();
			
			return response()->json([
				'message' => 'success',
				'html' => '',
				'ids' => ''
			]);
		}
		
		// Insert new record or UPDATE existing record with new user_ids
		// This will replace "2,4,6" with "2,6" automatically
		DB::statement("
			INSERT INTO inbox_users_manage (inbox_id, user_ids, table_name, added_user_id, brand_id) 
			VALUES (?, ?, ?, ?, ?) 
			ON DUPLICATE KEY UPDATE 
				user_ids = ?,
				added_user_id = ?",
			[
				$request->inbox_id,
				$userIds,
				$request->table,
				session('user_id'),
				session('brand_id'),
				$userIds,  // This replaces the old value
				session('user_id')
			]
		);
		
		$html = '';
		if (!empty($userIds)) {
			$userIdArray = array_filter(explode(',', $userIds));
			
			if (!empty($userIdArray)) {
				$userNames = DB::table('users')
					->whereIn('id', $userIdArray)
					->orderByRaw('FIELD(id, ' . implode(',', $userIdArray) . ')')
					->pluck('fullname');
				
				foreach ($userNames as $userName) {
					$html .= '<span class="badge bg-secondary me-2">'
						   . '<i class="fa-user fal me-1"></i>'
						   . '<span>' . e($userName) . '</span>'
						   . '</span>';
				}
			}
		}
		
		return response()->json([
			'message' => 'success',
			'html' => $html,
			'ids' => $userIds
		]);
	}

    // Helper methods would go here
    // These would need to be implemented based on your specific application logic
    
    protected function getInboxList()
    {
        return Inbox::getInboxList();
    }

    protected function getBrandsList()
    {
        $userId = session('user_id');
		
		$role = DB::table('users')->where('id', $userId)->value('role');
		if ((int)$role === 2) {
			// SUPER ADMIN: see every brand
			$brands = DB::table('brands')
				->orderBy('name')
				->get();
		} else {
			// Determine if this user is a team member and get effective team_id
			$memberRow = DB::table('team_members')
				->select('team_id')
				->where('uid', $userId)
				->first();

			$isMember = (bool) $memberRow;
			$teamId   = $isMember ? $memberRow->team_id : $userId;

			if (!$isMember) {
				// TEAM ADMIN: see all brands in this team
				$brands = DB::table('brands')
					->where('team_id', $teamId)
					->orderBy('name')
					->get();
			} else {
				// TEAM MEMBER: see brands created by me OR assigned to me (within team)
				$brands = DB::table('brands as b')
					->leftJoin('user_brands as ub', function ($join) use ($userId, $teamId) {
						$join->on('ub.brand_id', '=', 'b.id')
							 ->where('ub.user_id', '=', $userId)
							 ->where('ub.team_id', '=', $teamId);
					})
					->where('b.team_id', $teamId)
					->where(function ($q) use ($userId) {
						$q->where('b.user_id', $userId)      // created by me
						  ->orWhereNotNull('ub.user_id');     // assigned to me
					})
					->select('b.*')
					->distinct()
					->orderBy('b.name')
					->get();
			}
		}
		return $brands;
    }

   public function getUsersList()
{
    $search = request()->input('keyword');
    $userId = session('user_id');
    $role = DB::table('users')->where('id', $userId)->value('role');
    
    if ((int)$role === 2) {
        $query = DB::table('users')
            ->select('id', 'fullname', 'username', 'email', 'role')
            ->where('id', '!=', $userId)
            ->orderBy('fullname');
    } else {
        $memberRow = DB::table('team_members')
            ->select('team_id')
            ->where('uid', $userId)
            ->first();
        
        $isMember = (bool) $memberRow;
        $teamId = $isMember ? $memberRow->team_id : $userId;
        
        if (!$isMember) {
            $query = DB::table('users as u')
                ->leftJoin('team_members as tm', 'tm.uid', '=', 'u.id')
                ->select('u.id', 'u.fullname', 'u.username', 'u.email', 'u.role')
                ->where(function($q) use ($teamId) {
                    $q->where('tm.team_id', $teamId)
                      ->orWhere('u.id', $teamId);
                })
                ->where('u.id', '!=', $userId)
                ->distinct()
                ->orderBy('u.fullname');
        } else {
            $query = DB::table('users as u')
                ->join('team_members as tm', 'tm.uid', '=', 'u.id')
                ->select('u.id', 'u.fullname', 'u.username', 'u.email', 'u.role')
                ->where('tm.team_id', $teamId)
                ->where('u.id', '!=', $userId)
                ->orderBy('u.fullname');
        }
    }
    
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('fullname', 'like', "%$search%")
              ->orWhere('username', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%");
        });
    }
    
    // Return data directly, not a JSON response
    return $query->get();
}

public function getTagsList()
{
    $search = request()->input('keyword');
    $userId = session('user_id');
    $brandId = session('brand_id');
    $role = DB::table('users')->where('id', $userId)->value('role');
    
    if ((int)$role === 2) {
        // SUPER ADMIN: see all tags
        $query = DB::table('inbox_tags')
            ->select('id', 'tag_name', 'brand_id')  // Removed team_id
            ->orderBy('tag_name');
    } else {
        // Determine if this user is a team member and get effective team_id
        $memberRow = DB::table('team_members')
            ->select('team_id')
            ->where('uid', $userId)
            ->first();
        
        $isMember = (bool) $memberRow;
        $teamId = $isMember ? $memberRow->team_id : $userId;
        
        if (!$isMember) {
            // TEAM ADMIN: see tags in brands belonging to their team
            $query = DB::table('inbox_tags as t')
                ->join('brands as b', 'b.id', '=', 't.brand_id')
                ->select('t.id', 't.tag_name', 't.brand_id')  // Removed team_id
                ->where('b.team_id', $teamId);  // Filter by brand's team_id
            
            if ($brandId) {
                $query->where('t.brand_id', $brandId);
            }
            
            $query->orderBy('t.tag_name');
        } else {
            // TEAM MEMBER: see tags for brands they have access to
            $query = DB::table('inbox_tags as t')
                ->join('brands as b', 'b.id', '=', 't.brand_id')
                ->leftJoin('user_brands as ub', function ($join) use ($userId, $teamId) {
                    $join->on('ub.brand_id', '=', 'b.id')
                         ->where('ub.user_id', '=', $userId)
                         ->where('ub.team_id', '=', $teamId);
                })
                ->select('t.id', 't.tag_name', 't.brand_id')  // Removed team_id
                ->where('b.team_id', $teamId)  // Filter by brand's team_id
                ->where(function ($q) use ($userId, $brandId) {
                    $q->where('b.user_id', $userId)
                      ->orWhereNotNull('ub.user_id');
                    
                    if ($brandId) {
                        $q->where('t.brand_id', $brandId);
                    }
                })
                ->distinct()
                ->orderBy('t.tag_name');
        }
    }
    
    if ($search) {
        $query->where('t.tag_name', 'like', "%$search%");
    }
    
    return $query->get();
}
    protected function getInboxData($wheres, $whereIn)
    {
        return Inbox::getInboxList($wheres, $whereIn);
    }

    protected function getInboxCommentsData($wheres, $whereIn)
    {
        return InboxComment::getInboxCommentsList($wheres, $whereIn);
    }

    protected function generatePagination($page, $totalPages)
    {
        $pagerContainer = '<div class="pagination">';
        
        if ($page == 1) {
            $pagerContainer .= '';
        } else {
            $pagerContainer .= sprintf('<a href="javascript:void(0);" onclick="pagechange(%d)" style="color: #c00">&#171; prev </a>', $page - 1);
        }
        
        $pagerContainer .= ' <span class="px-3"> page <strong>' . $page . '</strong> from ' . $totalPages . '</span>';
        
        if ($page == $totalPages) {
            $pagerContainer .= '';
        } else {
            $pagerContainer .= sprintf('<a href="javascript:void(0);" onclick="pagechange(%d)" style="color: #c00"> next &#187; </a>', $page + 1);
        }
        
        $pagerContainer .= '</div>';
        
        return $pagerContainer;
    }

    protected function getDetailView($item, $wheres, $whereIn)
    {
        // This method would generate the detail view based on the item type
        // Implementation depends on your view structure
        if (empty($item['conversation_id'])) {
            return $this->getCommentDetailView($item, $wheres, $whereIn);
        } else {
            return $this->getMessageDetailView($item, $wheres, $whereIn);
        }
    }

    protected function getCommentDetailView($item, $wheres, $whereIn)
    {
        // Get inbox detail to fetch account_id and user IDs
        $inboxDetail = DB::table('inbox_comments as c')
            ->select('c.account_id', 'c.from_user_id', 'c.to_user_id','a.token')
			->leftJoin('accounts as a', 'a.id', '=', 'c.account_id')
            ->where('c.id', $item['id'])
            ->first();

        if (!$inboxDetail) {
            return '';
        }

        // Get comment details
        $commentWheres = [
            'post_id' => $item['post_id'],
            'is_child' => 0
        ];
        
        $inboxLists = InboxComment::getInboxCommentsDetail($commentWheres, []);
        
        // Get child comments for each parent comment
        if (!empty($inboxLists)) {
            $inboxArray = json_decode(json_encode($inboxLists), true);
            foreach ($inboxArray as $key => $comment) {
                if (!empty($comment['comment_count']) && $comment['comment_count'] > 0) {
                    $childWheres = [
                        'post_id' => $item['post_id'],
                        'parent_id' => $comment['message_id'],
                        'is_child' => 1
                    ];
                    $children = InboxComment::getInboxCommentsDetail($childWheres, []);
                    $inboxArray[$key]['child'] = json_decode(json_encode($children), true);
                } else {
                    $inboxArray[$key]['child'] = [];
                }
            }
        } else {
            $inboxArray = [];
        }

        // Get post details based on media type
        $postDetail = [];
        if ($item['media_type'] == 'facebook') {
            if (!in_array($item['inbox_type'], ['Comment', 'AdComment'])) {
                $postDetail = [];
            } else {
                $postDetail = $this->getPostDetail($item['post_id'],$inboxDetail->token);
            }
        } elseif ($item['media_type'] == 'linkedin') {
            $postDetail = [];
        } else {
            // Instagram or other
            $postDetail = $this->getPostDetailInsta($item['post_id'],$inboxDetail->token);
        }
		
        return view('appinbox::ajax_list_comment_detail', [
            'post_detail' => $postDetail,
            'lists' => $inboxArray,
            'id' => $item['id'],
            'conversation_id' => '',
			'mediaType' => $item['media_type'],
			'inboxType' => $item['inbox_type']
        ])->render();
    }

    protected function getMessageDetailView($item, $wheres, $whereIn)
    {
        // Get inbox detail to fetch account_id and user IDs
        $inboxDetail = DB::table('inbox')
            ->select('account_id', 'from_user_id', 'to_user_id')
            ->where('id', $item['id'])
            ->first();

        if (!$inboxDetail) {
            return '';
        }

        // Build where conditions for conversation
        $conversationWheres = [
            'conversation_id' => $item['conversation_id'],
            'account_id' => $inboxDetail->account_id
        ];

        // Get full conversation list
        $inboxLists = Inbox::getInboxDetail(
            $conversationWheres,
            [],
            $inboxDetail->from_user_id,
            $inboxDetail->to_user_id
        );

        // Convert to array
        $inboxArray = json_decode(json_encode($inboxLists), true);

        // Update last reviewed information
        DB::table('inbox')
            ->where('id', $item['id'])
            ->update([
                'last_reviewed_user_id' => session('user_id'),
                'last_reviewed_date' => now()
            ]);

        return view('appinbox::ajax_list_detail', [
            'lists' => $inboxArray,
            'id' => $item['id'],
            'conversation_id' => $item['conversation_id']
        ])->render();
    }

    protected function getAccountsByBrand($brandDetailData)
    {
        // Implementation to get accounts
		return Accounts::where("brand_id", session('brand_id'))->get();
    }

    protected function getBrandName($brandId)
    {
        $brand = DB::table('brands')->where('id', $brandId)->first();
        return $brand ? $brand->name : '';
    }

    protected function getUserName($userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        return $user ? $user->fullname : '';
    }

    protected function getTagName($tagId)
    {
        $tag = DB::table('inbox_tags')->where('id', $tagId)->first();
        return $tag ? $tag->tag_name : '';
    }

    protected function getProfileName($accountId)
    {
        return DB::table('accounts')->where('id', $accountId)->first();
    }

    protected function generateProfileIcon($profile)
    {
        // Generate profile icon HTML
        return '';
    }

    protected function generateNetworkIcon($network)
    {
        // Generate network icon HTML
        return '';
    }

    protected function getPostDetail($postId, $token)
    {
        // Implementation for getting Facebook post details
        return InboxComment::getPostDetail($postId, $token);
    }

    protected function getPostDetailInsta($postId, $token)
    {
        // Implementation for getting Instagram post details
        return InboxComment::getPostDetailInsta($postId, $token);
    }
	
	protected function getPostDetailLinkedin($postId, $token)
    {
        // Implementation for getting linkedin post details
        return Inbox::getPostDetailLinkedin($postId, $token);
    }	

    protected function postComment($id, $comment, $conversationId, $completeId)
    {
        // Implementation for posting comment
		$inbox = InboxComment::where('id', $id)->get()->toArray();
		$account = Accounts::where("id", $inbox[0]['account_id'])->get();
		
		if($account[0]['social_network'] == 'linkedin'){
			$inbox_list = Inbox::postLinkedInComment($account[0]->token,$inbox[0]['post_id'],$inbox[0]['from_user_id'],$comment,$completeId,$id);
		}else{
			if($account[0]['social_network'] == 'facebook'){
				$endpoint = "/".$inbox[0]['message_id']."/comments";
				$token = $account[0]->token;
			}else{
				$endpoint = "/".$inbox[0]['message_id']."/replies";
				$token = $account[0]->token;
			}
			return InboxComment::postComment($token, $comment, $conversationId, $completeId, $endpoint,$id); 
		}
    }

    protected function postMessage($id, $message, $conversationId, $completeId)
    {
        // Implementation for posting message
		$inbox = Inbox::where('id', $id)->get()->toArray();
		$account = Accounts::where("id", $inbox[0]['account_id'])->get();				
		
		if($account[0]['social_network'] == 'facebook'){
			$endpoint = "/me/messages";
			$token = $account[0]->token;
		}else{
			$endpoint = "/me/messages";
			$token = $account[0]->fbtoken;
		}
		return Inbox::postMessage($token, $inbox[0], $message, $conversationId, $completeId, $endpoint);
		       
    }

    protected function postLinkedinComment($accountId, $id, $comment, $conversationId, $completeId)
    {
        // Implementation for posting LinkedIn comment
        return ['status' => 'success', 'message' => 'LinkedIn comment posted'];
    }

    protected function postTwitterMessage($accountId, $id, $comment, $conversationId, $completeId)
    {
        // Implementation for posting Twitter message
        return ['status' => 'success', 'message' => 'Twitter message posted'];
    }

    
    protected function getInboxCount($brandId)
    {
        return DB::table('inbox')
            ->where('brand_id', $brandId)
            ->where('is_completed', 0)
            ->where('is_deleted', 0)
            ->count();
    }
	
	public function cron()
    {
		$messages = Inbox::get_message_conversation();
		$comments = InboxComment::getComments();		
		$mentions = Inbox::get_mentions();
		$reviews = Inbox::get_reviews();
		$ad_comments = Inbox::get_ad_comments();
		$linkedin_comment = Inbox::get_linkedin_comments();
		//$tiktok_messages = Inbox::get_tiktok_message_conversation();
	}
	
	public function verify(Request $request)
    {
        $challenge = $request->query('challenge');
        
        Log::info('[TikTok Webhook] Verification request', [
            'challenge' => $challenge,
            'all_params' => $request->all()
        ]);
        
        if ($challenge) {
            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }
        
        return response()->json(['error' => 'No challenge provided'], 400);
    }
    
    /**
     * Handle incoming webhooks (POST request)
     */
    public function handleMessage(Request $request)
    {
        try {
            // Log raw payload for debugging
            Log::info('[TikTok Webhook] Raw payload received', [
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);
            
            // Verify the signature
            if (!$this->verifySignature($request)) {
                Log::warning('[TikTok Webhook] Invalid signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            
            $payload = $request->all();
            
            // Extract event type
            $eventType = $payload['event_type'] ?? $payload['type'] ?? '';
            
            Log::info('[TikTok Webhook] Processing event', [
                'event_type' => $eventType
            ]);
            
            // Route to appropriate handler
            switch ($eventType) {
                case 'message.received':
                case 'message_received':
                    return $this->handleIncomingMessage($payload);
                    
                case 'message.sent':
                case 'message_sent':
                    return $this->handleOutgoingMessage($payload);
                    
                default:
                    Log::info('[TikTok Webhook] Unhandled event type', [
                        'type' => $eventType,
                        'payload' => $payload
                    ]);
                    return response()->json(['status' => 'event_type_not_handled'], 200);
            }
            
        } catch (\Exception $e) {
            Log::error('[TikTok Webhook] Processing error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Always return 200 to prevent TikTok from retrying
            return response()->json(['status' => 'error_logged'], 200);
        }
    }
    
    /**
     * Verify TikTok webhook signature
     */
    protected function verifySignature(Request $request): bool
    {
        // Get signature from headers
        $signature = $request->header('X-TikTok-Signature') 
                  ?? $request->header('X-Signature')
                  ?? $request->header('Signature');
                  
        $timestamp = $request->header('X-TikTok-Timestamp')
                  ?? $request->header('X-Timestamp')
                  ?? $request->header('Timestamp');
        
        if (!$signature || !$timestamp) {
            Log::warning('[TikTok Webhook] Missing signature or timestamp', [
                'signature' => $signature ? 'present' : 'missing',
                'timestamp' => $timestamp ? 'present' : 'missing',
                'all_headers' => $request->headers->all()
            ]);
            
            // For testing, temporarily return true
            // TODO: Change to false in production after testing
            return true;
        }
        
        // Check timestamp to prevent replay attacks
        $currentTime = time();
        if (abs($currentTime - $timestamp) > 300) { // 5 minutes
            Log::warning('[TikTok Webhook] Timestamp too old', [
                'current' => $currentTime,
                'received' => $timestamp,
                'diff' => abs($currentTime - $timestamp)
            ]);
            return false;
        }
        
        // Get webhook secret from options table (like your Facebook implementation)
        $webhookSecret = $this->getOption('tiktok_webhook_secret', '');
        
        if (empty($webhookSecret)) {
            Log::error('[TikTok Webhook] Webhook secret not configured in options table');
            return false;
        }
        
        // Calculate expected signature
        $body = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $body, $webhookSecret);
        
        $isValid = hash_equals($expectedSignature, $signature);
        
        if (!$isValid) {
            Log::warning('[TikTok Webhook] Signature mismatch', [
                'expected' => $expectedSignature,
                'received' => $signature
            ]);
        }
        
        return $isValid;
    }
    
    /**
     * Handle incoming message (user sent message to your account)
     */
    protected function handleIncomingMessage(array $payload)
    {
        try {
            $data = $payload['data'] ?? $payload;
            
            // Extract identifiers
            $ownerOpenId = $data['conversation']['owner_id'] 
                        ?? $data['recipient']['open_id']
                        ?? $data['owner_id']
                        ?? null;
            
            if (!$ownerOpenId) {
                Log::warning('[TikTok Webhook] No owner_id found', [
                    'payload_keys' => array_keys($payload),
                    'data_keys' => array_keys($data)
                ]);
                return response()->json(['status' => 'no_owner_id'], 200);
            }
            
            // Find account in database (matching your structure)
            $account = DB::table('accounts')
                ->where('social_network', 'tiktok')
                ->where('pid', $ownerOpenId)
                ->first();
            
            if (!$account) {
                Log::warning('[TikTok Webhook] Account not found', [
                    'owner_id' => $ownerOpenId,
                    'existing_tiktok_accounts' => DB::table('accounts')
                        ->where('social_network', 'tiktok')
                        ->pluck('pid', 'id')
                        ->toArray()
                ]);
                return response()->json(['status' => 'account_not_found'], 200);
            }
            
            Log::info('[TikTok Webhook] Account found', [
                'account_id' => $account->id,
                'brand_id' => $account->brand_id,
                'username' => $account->username
            ]);
            
            // Extract message data
            $message = $data['message'] ?? [];
            $conversation = $data['conversation'] ?? [];
            $sender = $data['sender'] ?? [];
            
            // Determine message direction
            $senderId = $message['sender_id'] ?? $sender['open_id'] ?? '';
            $totype = ($senderId == $account->pid) ? 'me' : '';
            
            // Set avatar images (using your helper function pattern)
            if ($senderId == $account->pid) {
                $from_image = $account->avatar ?? '';
                $to_image = theme_public_asset('img/default.png');
            } else {
                $from_image = theme_public_asset('img/default.png');
                $to_image = $account->avatar ?? '';
            }
            
            // Prepare inbox data (matching your exact structure)
            $inboxData = [
                'user_id' => '1',
                'account_id' => $account->id,
                'post_id' => '',
                'brand_id' => $account->brand_id,
                'team_id' => $account->team_id,
                'conversation_id' => $conversation['conversation_id'] ?? $data['conversation_id'] ?? '',
                'media_type' => 'tiktok',
                'inbox_type' => 'Messenger',
                'message' => $message['text'] ?? $message['content'] ?? '',
                'story' => '',
                'shares' => '',
                'attachments' => $this->extractAttachments($message),
                'from_name' => $sender['display_name'] ?? $sender['username'] ?? 'Unknown',
                'from_user_id' => $senderId,
                'to_name' => $account->name ?? $account->username ?? '',
                'to_type' => $totype,
                'to_user_id' => $account->pid,
                'from_image' => $from_image,
                'to_image' => $to_image,
                'message_id' => $message['message_id'] ?? $message['id'] ?? uniqid('tiktok_msg_'),
                'created_time' => $this->parseTimestamp($message['create_time'] ?? $message['created_at'] ?? null),
            ];
            
            // Save to database (matching your pattern)
            $exists = DB::table('inbox')->where('message_id', $inboxData['message_id'])->count();
            
            if ($exists) {
                // Update existing record
                DB::table('inbox')
                    ->where('message_id', $inboxData['message_id'])
                    ->update($inboxData);
                    
                Log::info('[TikTok Webhook] Message updated', [
                    'message_id' => $inboxData['message_id']
                ]);
            } else {
                try {
                    // Insert new record
                    DB::table('inbox')->insert($inboxData);
                    
                    Log::info('[TikTok Webhook] New message inserted', [
                        'message_id' => $inboxData['message_id']
                    ]);
                } catch (\Exception $e) {
                    Log::error('[TikTok Webhook] Insert failed: ' . $e->getMessage());
                }
            }
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('[TikTok Webhook] Error handling incoming message', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return response()->json(['status' => 'error'], 200);
        }
    }
    
    /**
     * Handle outgoing message
     */
    protected function handleOutgoingMessage(array $payload)
    {
        Log::info('[TikTok Webhook] Outgoing message received', [
            'payload' => $payload
        ]);
        
        return response()->json(['status' => 'success'], 200);
    }
    
    /**
     * Extract attachment URLs from message
     */
    protected function extractAttachments(array $message): string
    {
        if (empty($message['attachments'])) {
            return '';
        }
        
        foreach ($message['attachments'] as $attachment) {
            if (!empty($attachment['image_url'])) {
                return $attachment['image_url'];
            }
            if (!empty($attachment['video_url'])) {
                return $attachment['video_url'];
            }
            if (!empty($attachment['file_url'])) {
                return $attachment['file_url'];
            }
        }
        
        return '';
    }
    
    /**
     * Parse TikTok timestamp to MySQL datetime
     */
    protected function parseTimestamp($timestamp): string
    {
        if (empty($timestamp)) {
            return date('Y-m-d H:i:s');
        }
        
        // If timestamp is Unix time (integer)
        if (is_numeric($timestamp)) {
            return date('Y-m-d H:i:s', $timestamp);
        }
        
        // If timestamp is ISO 8601 string
        try {
            return date('Y-m-d H:i:s', strtotime($timestamp));
        } catch (\Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Get option from options table (matching your pattern)
     */
    protected function getOption(string $name, $default = '')
    {
        $option = DB::table('options')
            ->where('name', $name)
            ->first();
        
        return $option ? $option->value : $default;
    }
}