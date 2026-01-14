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

    /**
     * Get inbox list via AJAX
     */
    public function ajaxList(Request $request)
    {
        $wheres = [];
        $whereIn = [];
        $filterText = '';

        // Base conditions
        $wheres['to_type'] = 'me';
        $wheres['is_deleted'] = 0;

        // Item Filter (Inbox, Completed, Pending)
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

        // Brand filter
        if ($request->has('brand') && !empty($request->brand)) {
            $whereIn['brand_id'] = $request->brand;
            foreach ($request->brand as $brand) {
                $brandName = $this->getBrandName($brand);
                $filterText .= '<li class="" data-toggle="tooltip" data-placement="top" title="' . $brandName . '"><div class="badge bg-primary pl-2 pr-1 me-2"><span class="me-1 text-nowrap">Brand:</span><span class="text-truncate me-3">' . $brandName . '</span><span class="flex-shrink-1 ml-2 pointer" onclick="close_filter(\'brand[]\',' . $brand . ')">x</span></div></li>';
            }
        } else {
            $wheres['brand_id'] = session('brand_id');
        }

        // Users filter
        if ($request->has('users') && !empty($request->users)) {
            $whereIn['u2.id'] = $request->users;
            foreach ($request->users as $userId) {
                $userName = $this->getUserName($userId);
                $filterText .= '<li class="" data-toggle="tooltip" data-placement="top" title="' . $userName . '"><div class="badge bg-primary pl-2 pr-1 me-2"><span class="me-1 text-nowrap">User:</span><span class="text-truncate me-3">' . $userName . '</span><span class="flex-shrink-1 ml-2 pointer" onclick="close_filter(\'users[]\',' . $userId . ')">x</span></div></li>';
            }
        }

        // Tags filter
        if ($request->has('tags') && !empty($request->tags)) {
            if (in_array(0, $request->tags)) {
                $wheres['t.tag_ids'] = null;
            } else {
                $whereIn['t2.id'] = $request->tags;
            }

            foreach ($request->tags as $tag) {
                if ($tag == 0) {
                    $filterText .= '<li class="" data-toggle="tooltip" data-placement="top" title="Untag items"><div class="badge bg-primary pl-2 pr-1 me-2"><span class="me-1 text-nowrap">Tag:</span><span class="text-truncate me-3">Untag items</span><span class="flex-shrink-1 ml-2 pointer" onclick="close_filter(\'tags[]\',0)">x</span></div></li>';
                } else {
                    $tagName = $this->getTagName($tag);
                    $filterText .= '<li class="" data-toggle="tooltip" data-placement="top" title="' . $tagName . '"><div class="badge bg-primary pl-2 pr-1 me-2"><span class="me-1 text-nowrap">Tag:</span><span class="text-truncate me-3">' . $tagName . '</span><span class="flex-shrink-1 ml-2 pointer" onclick="close_filter(\'tags[]\',' . $tag . ')">x</span></div></li>';
                }
            }
        }

        // Accounts filter
        if ($request->has('accounts') && !empty($request->accounts)) {
            $whereIn['account_id'] = $request->accounts;
            foreach ($request->accounts as $account) {
                $profile = $this->getProfileName($account);
                $icon = get_social_media_icon($profile->social_network);
                $filterText .= '<li class="" data-toggle="tooltip" data-placement="top" title="' . $profile->name . '"><div class="badge bg-primary pl-2 pr-1 me-2"><span class="me-1 text-nowrap">Profile:</span><span class="text-truncate me-3">' . $icon . '</span><span class="flex-shrink-1 ml-2 pointer" onclick="close_filter(\'accounts[]\',' . $account . ')">x</span></div></li>';
            }
        }

        // Event Type filter
        if ($request->has('eventType') && !empty($request->eventType)) {
            $events = [];
            $networkType = [];
            foreach ($request->eventType as $et) {
                $parts = explode('_', $et);
                $events[] = $parts[1] ?? '';
                $networkType[] = $parts[0] ?? '';
                
                $icon = $this->generateNetworkIcon($parts[0] ?? '');
                $filterText .= '<li class="" data-toggle="tooltip" data-placement="top" title="' . ($parts[1] ?? '') . '"><div class="badge bg-primary pl-2 pr-1 me-2"><span class="me-1 text-nowrap">Type:</span><span class="text-truncate me-3">' . $icon . '</span><span class="flex-shrink-1 ml-2 pointer" onclick="close_filter(\'eventType[]\',\'' . $et . '\')">x</span></div></li>';
            }
            $whereIn['media_type'] = $networkType;
            $whereIn['inbox_type'] = $events;
        }

        // Date range filter
        if ($request->has('dateRange') && !empty($request->dateRange)) {
            $dateRange = explode(',', $request->dateRange);
            if (count($dateRange) == 2) {
                // CORRECT - Use simple keys
				$wheres['date_start'] = date('Y-m-d 00:00:00', strtotime($dateRange[0]));
				$wheres['date_end'] = date('Y-m-d 23:59:59', strtotime($dateRange[1]));
                $filterText .= '<li class="" data-toggle="tooltip" data-placement="top" title="' . $request->dateRange . '"><div class="badge bg-primary pl-2 pr-1 me-2"><span class="me-1 text-nowrap">Date:</span><span class="text-truncate me-3">' . $request->dateRange . '</span><span class="flex-shrink-1 ml-2 pointer" onclick="close_filter(\'dateRange\',\'' . $request->dateRange . '\')">x</span></div></li>';
            }
        }

        // Favourite filter
        if ($request->has('itemFav') && !empty($request->itemFav)) {
            if ($request->itemFav == 'Favourite') {
                $wheres['is_favourite'] = 1;
            }
        }

        // Get inbox data
        $inboxData = $this->getInboxData($wheres, $whereIn);		
        $inboxComments = $this->getInboxCommentsData($wheres, $whereIn);

        // Convert collections to arrays (stdClass objects need to be cast to array)
        $inboxArray = json_decode(json_encode($inboxData), true);
        $commentsArray = json_decode(json_encode($inboxComments), true);

        // Merge and sort
        $inboxList = array_merge($inboxArray, $commentsArray);
        usort($inboxList, function ($a, $b) {
            return strtotime($b['created_time']) - strtotime($a['created_time']);
        });

        // Pagination
        $page = $request->input('page', 1);
        $totalRecords = count($inboxList);
        $totalPages = ceil($totalRecords / $this->perPage);
        $offset = ($page - 1) * $this->perPage;
        $inboxList = array_slice($inboxList, $offset, $this->perPage);

        // Generate pagination
        $pagerContainer = $this->generatePagination($page, $totalPages);

        // Get detail view for first item
        $detailView = '';
        if (!empty($inboxList)) {
            $detailView = $this->getDetailView($inboxList[0], $wheres, $whereIn);
        }

        return response()->json([
            'filter_text' => $filterText,
            'list' => view('appinbox::ajax_list', [
                'inbox_list' => $inboxList,
                'page' => $page,
                'pagerContainer' => $pagerContainer
            ])->render(),
            'list_detail' => $detailView
        ]);
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
		//$messages = Inbox::get_message_conversation(2);
		//$comments = InboxComment::getComments(2);		
		//$mentions = Inbox::get_mentions(2);
		//$reviews = Inbox::get_reviews(2);
		//$ad_comments = Inbox::get_ad_comments(2);
		$linkedin_comment = Inbox::get_linkedin_comments(2);
	}
}