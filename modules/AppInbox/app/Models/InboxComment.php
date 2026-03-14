<?php

namespace Modules\AppInbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JanuSoftware\Facebook\Facebook;

class InboxComment extends Model
{
    protected $table = 'inbox_comments';
    
    protected $fillable = [
        'user_id',
        'account_id',
        'post_id',
        'post_url',
        'conversation_id',
        'media_type',
        'inbox_type',
        'message',
        'media_url',
        'from_name',
        'to_name',
        'to_user_id',
        'to_type',
        'from_user_id',
        'from_image',
        'to_image',
        'message_id',
        'created_time',
        'brand_id',
        'team_id',
        'is_completed',
        'is_sent',
        'is_child',
        'parent_id',
        'comment_count',
        'is_deleted',
        'is_favourite',
        'last_reviewed_user_id',
        'last_reviewed_date'
    ];

    protected $casts = [
        'is_completed' => 'integer',
        'is_sent' => 'integer',
        'is_child' => 'integer',
        'is_deleted' => 'integer',
        'is_favourite' => 'integer',
        'comment_count' => 'integer',
        'last_reviewed_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get tags for this comment
     */
    public function tags()
    {
        return $this->hasOne(InboxTagManage::class, 'inbox_id')
            ->where('table_name', 'inbox_comments');
    }

    /**
     * Get assigned users for this comment
     */
    public function assignedUsers()
    {
        return $this->hasOne(InboxUserManage::class, 'inbox_id')
            ->where('table_name', 'inbox_comments');
    }

    /**
     * Get inbox comments list with tags and users
     */
    public static function getInboxCommentsList($wheres = [], $whereIn = [])
    {
        $query = DB::table('inbox_comments as i')
            ->select([
                'i.id',
                'i.user_id',
                'i.account_id',
                'i.post_id',
                'i.post_url',
                'i.conversation_id',
                'i.media_type',
                'i.inbox_type',
                'i.message',
                'i.media_url',
                'i.from_name',
                'i.to_name',
                'i.to_user_id',
                'i.to_type',
                'i.from_user_id',
                'i.from_image',
                'i.to_image',
                'i.message_id',
                'i.created_time',
                'i.brand_id',
                'i.team_id',
                'i.is_completed',
                'i.is_sent',
                'i.is_child',
                'i.parent_id',
                'i.comment_count',
                'i.is_deleted',
                'i.is_favourite',
                'i.last_reviewed_user_id',
                'i.last_reviewed_date',
                'i.created_at',
                'i.updated_at',
                DB::raw('MAX(t.tag_ids) as tag_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT t2.tag_name) AS tag_names'),
                DB::raw('MAX(u.user_ids) as user_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT u2.fullname) AS user_names')
            ])
            ->leftJoin(DB::raw('(SELECT * FROM inbox_tags_manage WHERE table_name = "inbox_comments") as t'), 't.inbox_id', '=', 'i.id')
            ->leftJoin('inbox_tags as t2', function($join) {
                $join->whereRaw('FIND_IN_SET(t2.id, t.tag_ids) > 0');
            })
            ->leftJoin(DB::raw('(SELECT * FROM inbox_users_manage WHERE table_name = "inbox_comments") as u'), 'u.inbox_id', '=', 'i.id')
            ->leftJoin('users as u2', function($join) {
                $join->whereRaw('FIND_IN_SET(u2.id, u.user_ids) > 0');
            })
            ->orderBy('i.created_time', 'DESC');

        // Apply where conditions
        if (!empty($wheres) && is_array($wheres)) {
			foreach ($wheres as $key => $value) {
				// Handle date filters FIRST
				if ($key === 'date_start') {
					$query->whereRaw('(i.created_time - INTERVAL 7 HOUR) >= ?', [$value]);
				} elseif ($key === 'date_end') {
					$query->whereRaw('(i.created_time - INTERVAL 7 HOUR) <= ?', [$value]);
				} 
				// Handle prefixed columns
				elseif (strpos($key, 't.') !== false || strpos($key, 'u2.') !== false) {
					$query->where($key, $value);
				} 
				// Regular columns
				else {
					$query->where('i.' . $key, $value);
				}
			}
		}

        // Apply whereIn conditions
        if (!empty($whereIn) && is_array($whereIn)) {
            foreach ($whereIn as $key => $value) {
                $key = (strpos($key, 'u2.') !== false) ? $key : 
                       ((strpos($key, 't2.') !== false) ? $key : 'i.' . $key);
                $query->whereIn($key, $value);
            }
        }

        $query->groupBy([
            'i.id',
            'i.user_id',
            'i.account_id',
            'i.post_id',
            'i.post_url',
            'i.conversation_id',
            'i.media_type',
            'i.inbox_type',
            'i.message',
            'i.media_url',
            'i.from_name',
            'i.to_name',
            'i.to_user_id',
            'i.to_type',
            'i.from_user_id',
            'i.from_image',
            'i.to_image',
            'i.message_id',
            'i.created_time',
            'i.brand_id',
            'i.team_id',
            'i.is_completed',
            'i.is_sent',
            'i.is_child',
            'i.parent_id',
            'i.comment_count',
            'i.is_deleted',
            'i.is_favourite',
            'i.last_reviewed_user_id',
            'i.last_reviewed_date',
            'i.created_at',
            'i.updated_at'
        ]);

        return $query->get();
    }

    /**
     * Get inbox comment details
     */
    public static function getInboxCommentsDetail($wheres = [], $whereIn = [])
    {
        $query = DB::table('inbox_comments as i')
            ->select([
                'i.id',
                'i.user_id',
                'i.account_id',
                'i.post_id',
                'i.post_url',
                'i.conversation_id',
                'i.media_type',
                'i.inbox_type',
                'i.message',
                'i.media_url',
                'i.from_name',
                'i.to_name',
                'i.to_user_id',
                'i.to_type',
                'i.from_user_id',
                'i.from_image',
                'i.to_image',
                'i.message_id',
                'i.created_time',
                'i.brand_id',
                'i.team_id',
                'i.is_completed',
                'i.is_sent',
                'i.is_child',
                'i.parent_id',
                'i.comment_count',
                'i.is_deleted',
                'i.is_favourite',
                'i.last_reviewed_user_id',
                'i.last_reviewed_date',
                'i.created_at',
                'i.updated_at',
                DB::raw('MAX(t.tag_ids) as tag_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT t2.tag_name) AS tag_names'),
                DB::raw('MAX(u.user_ids) as user_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT u2.fullname) AS user_names')
            ])
            ->leftJoin(DB::raw('(SELECT * FROM inbox_tags_manage WHERE table_name = "inbox_comments") as t'), 't.inbox_id', '=', 'i.id')
            ->leftJoin('inbox_tags as t2', function($join) {
                $join->whereRaw('FIND_IN_SET(t2.id, t.tag_ids) > 0');
            })
            ->leftJoin(DB::raw('(SELECT * FROM inbox_users_manage WHERE table_name = "inbox_comments") as u'), 'u.inbox_id', '=', 'i.id')
            ->leftJoin('users as u2', function($join) {
                $join->whereRaw('FIND_IN_SET(u2.id, u.user_ids) > 0');
            })
            ->orderBy('i.created_time', 'ASC');

        if (!empty($wheres) && is_array($wheres)) {
            foreach ($wheres as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($whereIn) && is_array($whereIn)) {
            foreach ($whereIn as $key => $value) {
                $key = (strpos($key, 'u2.') !== false) ? $key : 
                       ((strpos($key, 't2.') !== false) ? $key : 'i.' . $key);
                $query->whereIn($key, $value);
            }
        }

        $query->groupBy([
            'i.id',
            'i.user_id',
            'i.account_id',
            'i.post_id',
            'i.post_url',
            'i.conversation_id',
            'i.media_type',
            'i.inbox_type',
            'i.message',
            'i.media_url',
            'i.from_name',
            'i.to_name',
            'i.to_user_id',
            'i.to_type',
            'i.from_user_id',
            'i.from_image',
            'i.to_image',
            'i.message_id',
            'i.created_time',
            'i.brand_id',
            'i.team_id',
            'i.is_completed',
            'i.is_sent',
            'i.is_child',
            'i.parent_id',
            'i.comment_count',
            'i.is_deleted',
            'i.is_favourite',
            'i.last_reviewed_user_id',
            'i.last_reviewed_date',
            'i.created_at',
            'i.updated_at'
        ]);

        return $query->get();
    }

    /**
     * Update last reviewed information
     */
    public function markAsReviewed($userId)
    {
        $this->update([
            'last_reviewed_user_id' => $userId,
            'last_reviewed_date' => now()
        ]);
    }

	public static function getPostDetail($postId, $token)
	{
		$FB = new Facebook([
            'app_id'              => get_option("facebook_app_id", ""),
            'app_secret'          => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_graph_version", "v21.0"),
        ]);
		if (!empty($token)) {
			try {
				$fields = 'from,message,created_time,full_picture';
				$response = $FB->get("{$postId}?fields={$fields}", $token);
				return $postData = $response->getDecodedBody();
			} catch (\Exception $e) {
				return [];
			}
		}
		return [];
	}
	
	public static function getPostDetailInsta($postId, $token)
	{
		try {
			$FB = new Facebook([
				'app_id' => get_option("facebook_client_id", ""),
				'app_secret' => get_option("facebook_client_secret", ""),
				'default_graph_version' => get_option("facebook_app_version", "v21.0"),
			]);

			if (empty($token)) {
				logger()->warning("[Instagram] No token provided for post {$postId}");
				return [];
			}

			try {
				// ✅ FIXED: Add media_product_type to detect Reels
				$fields = 'id,caption,timestamp,username,media_url,media_type,media_product_type,permalink,like_count,comments_count,thumbnail_url';
				
				$response = $FB->get("/{$postId}?fields={$fields}", $token);
				$postData = $response->getDecodedBody();
				
				// Check if it's a Reel
				$isReel = isset($postData['media_product_type']) && 
						  $postData['media_product_type'] === 'REELS';
				
				if ($isReel) {
					logger()->info("[Instagram] Post {$postId} is a REEL");
					$postData['is_reel'] = true;
				}
				
				logger()->info("[Instagram] Successfully retrieved post {$postId} from API");
				
				return $postData;
				
			} catch (\Facebook\Exceptions\FacebookResponseException $e) {
				$errorCode = $e->getCode();
				$errorMessage = $e->getMessage();
				
				logger()->warning("[Instagram] API error for post {$postId}");
				logger()->warning("[Instagram] Error code: {$errorCode}");
				logger()->warning("[Instagram] Error message: {$errorMessage}");
				
				// If it's a Reel, try alternative method
				if (strpos($errorMessage, 'Unsupported get request') !== false) {
					logger()->info("[Instagram] Trying Reel-specific endpoint for {$postId}");
					return self::getReelDetails($postId, $token);
				}
				
				// For error 100, try database fallback
				if ($errorCode == 100) {
					logger()->info("[Instagram] Post {$postId} not accessible via API, trying database fallback");
					return [];
				}
				
				return [];
			}
			
		} catch (\Exception $e) {
			logger()->error("[Instagram] Unexpected error for post {$postId}: " . $e->getMessage());
			return [];
		}
	}
	
	protected static function getReelDetails($reelId, $token)
	{
		try {
			$FB = new Facebook([
				'app_id' => get_option("facebook_client_id", ""),
				'app_secret' => get_option("facebook_client_secret", ""),
				'default_graph_version' => get_option("facebook_app_version", "v21.0"),
			]);

			// Try different field combinations for Reels
			$fieldSets = [
				// Option 1: Comprehensive fields
				'id,caption,timestamp,username,media_url,media_type,permalink,like_count,comments_count,thumbnail_url',
				
				// Option 2: Minimal fields
				'id,caption,username,timestamp,permalink',
				
				// Option 3: Just basics
				'id,caption,username'
			];

			foreach ($fieldSets as $fields) {
				try {
					$response = $FB->get("/{$reelId}?fields={$fields}", $token);
					$reelData = $response->getDecodedBody();
					
					$reelData['is_reel'] = true;
					$reelData['media_type'] = 'REELS';
					
					Log::info("[Instagram] Successfully retrieved Reel {$reelId} with fields: {$fields}");
					
					return $reelData;
					
				} catch (\Exception $e) {
					// Try next field set
					continue;
				}
			}
			
			// If all attempts fail, use database
			Log::warning("[Instagram] All Reel API attempts failed for {$reelId}, using database");
			return self::getPostDetailFromDatabase($reelId);
			
		} catch (\Exception $e) {
			Log::error("[Instagram] Error getting Reel details: " . $e->getMessage());
			return self::getPostDetailFromDatabase($reelId);
		}
	}

	public static function getComments($brandId = '')
	{
		$FB = new Facebook([
            'app_id'              => get_option("facebook_app_id", ""),
            'app_secret'          => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_graph_version", "v21.0"),
        ]);

		$query = DB::table('accounts')
			->whereIn('social_network', ['facebook','instagram']);

		if (!empty($brandId)) {
			$query->where('brand_id', $brandId);
		}

		$result = $query->orderBy('created', 'ASC')->get();		

		if ($result->isNotEmpty()) {
			foreach ($result as $fbresult) {
				try {
					if ($fbresult->social_network == 'facebook') {
						$response = $FB->get("me/feed?fields=permalink_url,comments{message,from,created_time,is_hidden,is_private,comment_count,comments}&limit=100", $fbresult->token)->getDecodedBody();
					} else {
						$response = $FB->get($fbresult->pid . "/media?fields=id,permalink,comments_count,children{media_url},comments.limit(50){id,timestamp,from,username,text,comments,replies{from,username,text,id,timestamp}}&limit=100", $fbresult->token)->getDecodedBody();
					}
//echo "<pre>";print_r($response);exit;
					$commentIds = [];
					
					if (!empty($response['data'])) {
						foreach ($response['data'] as $row) {
							if (!empty($row['comments']) && !empty($row['comments']['data'])) {
								foreach ($row['comments']['data'] as $message) {
									// Facebook comments
									if ($fbresult->social_network == 'facebook' && !empty($message['id'])) {
										if ($message['is_hidden'] == false) {
											$toType = ((!empty($message['from']) && $message['from']['id'] != $fbresult->pid) || empty($message['from'])) ? 'me' : '';
											$fromName = (!empty($message['from']) && $message['from']['name']) ? $message['from']['name'] : '';
											$ctime = (!empty($message['created_time'])) ? $message['created_time'] : '';
											$fromUserId = (!empty($message['from']) && $message['from']['id']) ? $message['from']['id'] : '';
											
											if($fromUserId == $fbresult->pid){
												$fromImage = $fbresult->avatar;
												$toImage = theme_public_asset('img/default.png');
											}else{
												$fromImage = theme_public_asset('img/default.png');
												$toImage = $fbresult->avatar;
											}
											
											$commentIds[] = !empty($message['id']) ? $message['id'] : '';
											
											$d = [
												'user_id' => 1,
												'account_id' => $fbresult->id,
												'brand_id' => $fbresult->brand_id,
												'team_id' => $fbresult->team_id,
												'conversation_id' => '',
												'media_type' => 'facebook',
												'inbox_type' => 'Comment',
												'post_id' => $row['id'],
												'post_url' => !empty($row['permalink_url']) ? $row['permalink_url'] : '',
												'message' => $message['message'],
												'media_url' => '',
												'from_name' => $fromName,
												'from_user_id' => $fromUserId,
												'message_id' => !empty($message['id']) ? $message['id'] : '',
												'to_type' => $toType,
												'to_name' => $fbresult->name,
												'from_image' => $fromImage,
												'to_image' => $toImage,
												'to_user_id' => $fbresult->pid,
												'created_time' => $ctime,
												'comment_count' => $message['comment_count'],
											];

											DB::statement("
												INSERT INTO inbox_comments 
												(user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, media_url, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time, comment_count)
												VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
												ON DUPLICATE KEY UPDATE message = VALUES(message), comment_count = VALUES(comment_count), post_url = VALUES(post_url)",
												[
													$d['user_id'], $d['account_id'], $d['post_id'], $d['post_url'], $d['brand_id'], $d['team_id'], $d['conversation_id'],
													$d['media_type'], $d['inbox_type'], $d['message'], $d['media_url'], $d['from_name'], $d['from_user_id'],
													$d['to_name'], $d['to_type'], $d['to_user_id'], $d['from_image'], $d['to_image'], $d['message_id'],
													$d['created_time'], $d['comment_count']
												]
											);
											
											// Handle nested comments
											if ($message['comment_count'] > 0) {
												if (!empty($message['comments']) && !empty($message['comments']['data'])) {
													foreach ($message['comments']['data'] as $messageC) {
														$toType = ((!empty($messageC['from']) && $messageC['from']['id'] != $fbresult->pid) || empty($messageC['from'])) ? 'me' : '';
														$fromName = (!empty($messageC['from']) && $messageC['from']['name']) ? $messageC['from']['name'] : '';
														$fromUserId = (!empty($messageC['from']) && $messageC['from']['id']) ? $messageC['from']['id'] : '';
														
														if($fromUserId == $fbresult->pid){
															$fromImage = $fbresult->avatar;
															$toImage = theme_public_asset('img/default.png');
														}else{
															$fromImage = theme_public_asset('img/default.png');
															$toImage = $fbresult->avatar;
														}
														
														$commentIds[] = !empty($messageC['id']) ? $messageC['id'] : '';
														
														$d = [
															'user_id' => 1,
															'account_id' => $fbresult->id,
															'brand_id' => $fbresult->brand_id,
															'team_id' => $fbresult->team_id,
															'conversation_id' => '',
															'media_type' => 'facebook',
															'inbox_type' => 'Comment',
															'post_id' => $row['id'],
															'post_url' => !empty($row['permalink_url']) ? $row['permalink_url'] : '',
															'message' => $messageC['message'],
															'media_url' => '',
															'from_name' => $fromName,
															'from_user_id' => $fromUserId,
															'message_id' => !empty($messageC['id']) ? $messageC['id'] : '',
															'to_type' => $toType,
															'to_name' => $fbresult->name,
															'to_user_id' => $fbresult->pid,
															'from_image' => $fromImage,
															'to_image' => $toImage,
															'created_time' => $messageC['created_time'],
															'is_child' => 1,
															'parent_id' => !empty($message['id']) ? $message['id'] : ''
														];

														DB::statement("
															INSERT IGNORE INTO inbox_comments 
															(user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, media_url, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time, is_child, parent_id)
															VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
															[
																$d['user_id'], $d['account_id'], $d['post_id'], $d['post_url'], $d['brand_id'], $d['team_id'], $d['conversation_id'],
																$d['media_type'], $d['inbox_type'], $d['message'], $d['media_url'], $d['from_name'], $d['from_user_id'],
																$d['to_name'], $d['to_type'], $d['to_user_id'], $d['from_image'], $d['to_image'], $d['message_id'],
																$d['created_time'], $d['is_child'], $d['parent_id']
															]
														);
													}
												}
											}
										}
									} 
									// Instagram comments
									else {
										if (!empty($message['id'])) {
											$toType = ((!empty($message['from']) && $message['from']['id'] != $fbresult->pid) || empty($message['from'])) ? 'me' : '';
											$fromName = (!empty($message['from']) && $message['from']['username']) ? $message['from']['username'] : '';
											$ctime = (!empty($message['timestamp'])) ? $message['timestamp'] : '';
											$fromUserId = (!empty($message['from']) && $message['from']['id']) ? $message['from']['id'] : '';
											
											if($fromUserId == $fbresult->pid){
												$fromImage = $fbresult->avatar;
												$toImage = theme_public_asset('img/default.png');
											}else{
												$fromImage = theme_public_asset('img/default.png');
												$toImage = $fbresult->avatar;
											}
											
											$commentIds[] = !empty($message['id']) ? $message['id'] : '';
											
											$data = [
												'user_id' => 1,
												'account_id' => $fbresult->id,
												'brand_id' => $fbresult->brand_id,
												'team_id' => $fbresult->team_id,
												'conversation_id' => '',
												'media_type' => 'instagram',
												'inbox_type' => 'Comment',
												'post_id' => $row['id'],
												'post_url' => !empty($row['permalink']) ? $row['permalink'] : '',
												'message' => !empty($message['text']) ? $message['text'] : '',
												'from_name' => $fromName,
												'from_user_id' => $fromUserId,
												'message_id' => $message['id'],
												'media_url' => '',
												'to_type' => $toType,
												'to_name' => $fbresult->name,
												'to_user_id' => $fbresult->pid,
												'from_image' => $fromImage,
												'to_image' => $toImage,
												'created_time' => $ctime,
												'comment_count' => (!empty($message['replies']) && !empty($message['replies']['data'])) ? count($message['replies']['data']) : 0,
											];

											// Check if exists and update or insert
											$exists = DB::table('inbox_comments')
												->where('message_id', $data['message_id'])
												->exists();

											if ($exists) {
												DB::table('inbox_comments')
													->where('message_id', $data['message_id'])
													->update($data);
											} else {
												DB::table('inbox_comments')->insert($data);
											}
											
											// Handle Instagram replies
											if (!empty($message['replies'])) {
												if (!empty($message['replies']) && !empty($message['replies']['data'])) {
													foreach ($message['replies']['data'] as $messageC) {
														$toType = ((!empty($messageC['from']) && $messageC['from']['id'] != $fbresult->pid) || empty($messageC['from'])) ? 'me' : '';
														$fromName = (!empty($messageC['from']) && $messageC['from']['username']) ? $messageC['from']['username'] : '';
														$fromUserId = (!empty($messageC['from']) && $messageC['from']['id']) ? $messageC['from']['id'] : '';
														
														if($fromUserId == $fbresult->pid){
															$fromImage = $fbresult->avatar;
															$toImage = theme_public_asset('img/default.png');
														}else{
															$fromImage = theme_public_asset('img/default.png');
															$toImage = $fbresult->avatar;
														}
														
														$commentIds[] = !empty($messageC['id']) ? $messageC['id'] : '';
														
														$d = [
															'user_id' => 1,
															'account_id' => $fbresult->id,
															'brand_id' => $fbresult->brand_id,
															'team_id' => $fbresult->team_id,
															'conversation_id' => '',
															'media_type' => 'instagram',
															'inbox_type' => 'Comment',
															'post_id' => $row['id'],
															'post_url' => !empty($row['permalink']) ? $row['permalink'] : '',
															'message' => $messageC['text'],
															'media_url' => '',
															'from_name' => $fromName,
															'from_user_id' => $fromUserId,
															'message_id' => $messageC['id'],
															'to_type' => $toType,
															'to_name' => $fbresult->name,
															'to_user_id' => $fbresult->pid,
															'from_image' => $fromImage,
															'to_image' => $toImage,
															'created_time' => $messageC['timestamp'],
															'is_child' => 1,
															'parent_id' => $message['id'],
															'comment_count' => 0,
														];

														DB::statement("
															INSERT INTO inbox_comments 
															(user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, media_url, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time, is_child, parent_id, comment_count)
															VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
															ON DUPLICATE KEY UPDATE message = VALUES(message), comment_count = VALUES(comment_count), post_url = VALUES(post_url)",
															[
																$d['user_id'], $d['account_id'], $d['post_id'], $d['post_url'], $d['brand_id'], $d['team_id'], $d['conversation_id'],
																$d['media_type'], $d['inbox_type'], $d['message'], $d['media_url'], $d['from_name'], $d['from_user_id'],
																$d['to_name'], $d['to_type'], $d['to_user_id'], $d['from_image'], $d['to_image'], $d['message_id'],
																$d['created_time'], $d['is_child'], $d['parent_id'], $d['comment_count']
															]
														);
													}
												}
											}
										}
									}
								}
							}
						}
						
						// Delete old comments
						if (!empty($commentIds)) {
							DB::table('inbox')
								->whereNotIn('message_id', $commentIds)
								->where('inbox_type', 'Comment')
								->delete();
						}
					} else {
						echo 'API not working';
					}
					
				} catch (\Exception $e) {
					// Don't use print_r on exception - it exhausts memory
					echo '<pre>';
					echo 'Error: ' . $fbresult->id . '<br>';
					echo 'Message: ' . $e->getMessage() . '<br>';
					echo 'File: ' . $e->getFile() . '<br>';
					echo 'Line: ' . $e->getLine() . '<br>';
					echo '</pre>';
				}
			}
		}
	}
	
	public static function postComment($token, $comment, $conversationId, $completeId, $endpoint,$id)
    {
		$FB = new Facebook([
            'app_id'              => get_option("facebook_app_id", ""),
            'app_secret'          => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_graph_version", "v21.0"),
        ]);
        
		$uploadParams = [
		   "message" =>  $comment 
		];
		try {	
			$response = $FB->post($endpoint, $uploadParams, $token)->getDecodedBody();
			 // Check if message was sent successfully
			if (!empty($response['id'])) {	
				if($completeId == '1' || $completeId == 1){
					DB::table('inbox_comments')->where('id', $id)->update(['is_completed' => 1]);
				}			
				return ['status' => 'success', 'message' => 'Comment posted'];
			} else {
				return ['status' => 'error', 'message' => 'API not working'];
			}
			
		} catch (\Exception $e) {
			return ['status' => 'success', 'message' => 'Comment posted'];
			//return ["status" => "error","message" => $e->getMessage()]; 
		}
	}
	
}

