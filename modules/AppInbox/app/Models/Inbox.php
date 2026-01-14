<?php

namespace Modules\AppInbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use JanuSoftware\Facebook\Facebook;
use Modules\AppChannelLinkedinProfiles\Classes\LinkedinAPI;

class Inbox extends Model
{
    protected $table = 'inbox';
    
    protected $fillable = [
        'user_id',
        'account_id',
        'post_id',
        'conversation_id',
        'media_type',
        'inbox_type',
        'message',
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
        'is_deleted',
        'is_favourite',
        'last_reviewed_user_id',
        'last_reviewed_date',
        'story',
        'attachments',
        'shares'
    ];

    protected $casts = [
        'is_completed' => 'integer',
        'is_sent' => 'integer',
        'is_deleted' => 'integer',
        'is_favourite' => 'integer',
        'last_reviewed_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get tags for this inbox
     */
    public function tags()
    {
        return $this->hasOneThrough(
            InboxTagManage::class,
            InboxTag::class,
            'id',
            'inbox_id',
            'id',
            'id'
        )->where('table_name', 'inbox');
    }

    /**
     * Get assigned users for this inbox
     */
    public function assignedUsers()
    {
        return $this->hasOne(InboxUserManage::class, 'inbox_id')
            ->where('table_name', 'inbox');
    }

    /**
     * Get inbox list with tags and users
     */
    public static function getInboxList($wheres = [], $whereIn = [])
	{
		$query = DB::table('inbox as i')
			->select([
				'i.id',
				'i.user_id',
				'i.account_id',
				'i.post_id',
				'i.conversation_id',
				'i.media_type',
				'i.inbox_type',
				'i.message',
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
				'i.is_deleted',
				'i.is_favourite',
				'i.last_reviewed_user_id',
				'i.last_reviewed_date',
				'i.story',
				'i.attachments',
				'i.shares',
				'i.created_at',
				'i.updated_at',
				DB::raw('MAX(t.tag_ids) as tag_ids'),
				DB::raw('GROUP_CONCAT(DISTINCT t2.tag_name) AS tag_names'),
				DB::raw('MAX(u.user_ids) as user_ids'),
				DB::raw('GROUP_CONCAT(DISTINCT u2.fullname) AS user_names')
			])
			// FIX: Change "inbox" to "sp_inbox"
			->leftJoin(DB::raw('(SELECT * FROM inbox_tags_manage WHERE table_name = "sp_inbox") as t'), 't.inbox_id', '=', 'i.id')
			->leftJoin('inbox_tags as t2', function($join) {
				$join->whereRaw('FIND_IN_SET(t2.id, t.tag_ids) > 0');
			})
			// FIX: Change "inbox" to "sp_inbox"
			->leftJoin(DB::raw('(SELECT * FROM inbox_users_manage WHERE table_name = "sp_inbox") as u'), 'u.inbox_id', '=', 'i.id')
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
			'i.conversation_id',
			'i.media_type',
			'i.inbox_type',
			'i.message',
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
			'i.is_deleted',
			'i.is_favourite',
			'i.last_reviewed_user_id',
			'i.last_reviewed_date',
			'i.story',
			'i.attachments',
			'i.shares',
			'i.created_at',
			'i.updated_at'
		]);
		
		return $query->get();
	}

    /**
     * Get inbox conversation details
     */
    public static function getInboxDetail($wheres = [], $whereIn = [], $fromId = '', $toId = '')
    {
        $query = DB::table('inbox')
            ->select('*')
            ->orderBy('created_time', 'ASC');

        if (!empty($wheres) && is_array($wheres)) {
            foreach ($wheres as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($whereIn) && is_array($whereIn)) {
            foreach ($whereIn as $key => $value) {
                $query->whereIn($key, $value);
            }
        }

        if ($fromId && $toId) {
            $query->where(function($q) use ($fromId, $toId) {
                $q->where(function($subQ) use ($fromId, $toId) {
                    $subQ->where('from_user_id', $fromId)
                         ->where('to_user_id', $toId);
                })->orWhere(function($subQ) use ($fromId, $toId) {
                    $subQ->where('to_user_id', $fromId)
                         ->where('from_user_id', $toId);
                });
            });
        }

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
	
	public static function postMessage($token, $inbox, $message, $conversationId, $completeId, $endpoint)
    {
		$FB = new Facebook([
            'app_id'              => get_option("facebook_app_id", ""),
            'app_secret'          => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_graph_version", "v21.0"),
        ]);
		$uploadParams = [
		   "recipient" => ["id" => $inbox['from_user_id']], 
		   "messaging_type" => "MESSAGE_TAG", 
		   "tag" => "POST_PURCHASE_UPDATE",
		   "message" => [
				 "text" => $message 
			  ] 
		];	
		try {	
			$response = $FB->post($endpoint, $uploadParams, $token)->getDecodedBody();
			 // Check if message was sent successfully
			if (!empty($response['recipient_id'])) {	
				if($completeId == '1' || $completeId == 1){
					DB::table('inbox')->where('conversation_id', $conversationId)->update(['is_completed' => 1]);
				}			
				return ['status' => 'success', 'message' => 'Message posted'];
			} else {
				return ['status' => 'error', 'message' => 'API not working'];
			}
			
		} catch (\Exception $e) {
			return ["status" => "error","message" => $e->getMessage()]; 
		}
	}
	
	public static function get_message_conversation($brand_id = ''){ 
		$FB = new Facebook([
            'app_id'              => get_option("facebook_app_id", ""),
            'app_secret'          => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_graph_version", "v21.0"),
        ]);        
		
		$query = DB::table('accounts')
			->whereIn('social_network', ['facebook','instagram']);

		if (!empty($brand_id)) {
			$query->where('brand_id', $brand_id);
		}

		$result = $query->orderBy('created', 'ASC')->get();
		//echo "<pre>";print_r($result);exit;
		if(!empty($result)){
			foreach($result as $fbresult){
				try {
					if($fbresult->social_network == 'facebook'){
						$acc_tok = $fbresult->token;
						try {
            				$response = $FB->get( "me/conversations?fields=messages{message,from,id,created_time,to},id,unread_count", $fbresult->token)->getDecodedBody();
							//echo "<pre>";print_r($response);exit;
            			} catch (\Exception $e) {
            				$response = array();
							//echo "<pre>";print_r($e);exit;
            			}
					}else{
						$acc_tok = $fbresult->fbtoken;
					    try {
            				$response = $FB->get( $fbresult->fid."/conversations?platform=instagram&fields=messages{message,from,id,created_time,to,tags,attachments{file_url,image_data},story,shares},id,unread_count", $fbresult->fbtoken)->getDecodedBody();
							//echo "<pre>";print_r($response);exit;
            			} catch (\Exception $e) {							
							echo "<pre>";
							echo "Error: " . $e->getMessage();
							echo "\nFile: " . $e->getFile();
							echo "\nLine: " . $e->getLine();
							echo "</pre>";
							//exit;
            				$response = array();
            			}
					}
					if(!empty($response['data'])){
						$message_ids = array();
						foreach($response['data'] as $row){
							//if($fbresult->social_network == 'instagram' || $row['unread_count'] >= 1){
								if(!empty($row['messages']['data'])){
									foreach($row['messages']['data'] as $message){
										if(!empty($message['message'])){
										$totype = ($message['to']['data'][0]['id'] == $fbresult->pid) ? 'me' :'';
										if($fbresult->social_network == 'facebook'){
											$from_name = (!empty($message['from']) && $message['from']['name'])?$message['from']['name']:'';
											$to_name = (!empty($message['to']) && $message['to']['data'][0]['name'])?$message['to']['data'][0]['name']:'';
											$from_user_id = (!empty($message['from']) && $message['from']['id'])?$message['from']['id']:'';
											
											
											
										}else{
											$from_name = (!empty($message['from']) && $message['from']['username'])?$message['from']['username']:'';
											$to_name = (!empty($message['to']) && $message['to']['data'][0]['username'])?$message['to']['data'][0]['username']:'';
											$from_user_id = (!empty($message['from']) && $message['from']['id'])?$message['from']['id']:'';
										}
										
										
										if($from_user_id == $fbresult->pid){
											$from_image = $fbresult->avatar;
											$to_image = theme_public_asset('img/default.png');
										}else{
											$from_image = theme_public_asset('img/default.png');
											$to_image = $fbresult->avatar;
										}
											
										$data = [
											'user_id' => '1',
											'account_id' => $fbresult->id,
											'post_id' => '',
											'brand_id' => $fbresult->brand_id,
											'team_id' => $fbresult->team_id,
											'conversation_id' => $row['id'],
											'media_type' => $fbresult->social_network,
											'inbox_type' => 'Messenger',
											'message' => $message['message'],
											'story' => (!empty($message['story']['mention']['link']) ? $message['story']['mention']['link'] : ''),
											'shares' => (!empty($message['shares']['data'][0]['link']) ? $message['shares']['data'][0]['link'] : ''),
											'attachments' => (!empty($message['attachments']['data'][0]['image_data']['url']) ? $message['attachments']['data'][0]['image_data']['url'] : ''),
											'from_name' => $from_name,
											'from_user_id' => $from_user_id,
											'to_name' => $to_name,
											'to_type' => $totype,
											'to_user_id' => $message['to']['data'][0]['id'],
											'from_image' => $from_image,
											'to_image' => $to_image,
											'message_id' => $message['id'],
											'created_time' => $message['created_time'],
										];
										//echo "<pre>";print_r($data);exit;
										
										// Check if the record exists by message_id
										$exists = DB::table('inbox')->where('message_id', $data['message_id'])->count();
										//echo "<pre>";print_r($exists);exit;

										if ($exists) {
											// Update existing record
											DB::table('inbox')
												->where('message_id', $data['message_id'])
												->update($data);
										} else {
											try {
												
											   $result = DB::table('inbox')->insert($data);											   
										   } catch (\Exception $e) {
											   echo "Error: " . $e->getMessage();
											   //exit;
										   }
										}
										
										
										$message_ids[] = $message['id'];
										//db_insert(TB_INBOX, $data);
										
									}	
									}
								}
						}
						if (!empty($message_ids)) {
							// Delete messages
							DB::table('inbox')
								->whereNotIn('message_id', $message_ids)
								->where('inbox_type', 'Messenger')
								->where('media_type', $fbresult->social_network)
								->delete();
						}
					}else{
						//echo "<pre>";print_r($response);exit;
						//echo 'API not working';
					}
					
				} catch (\Exception $e) {
					//print_r($e);exit;
				}
			}
		}	 
    }
	public static function get_mentions($brandId = null)
{
    try {
        $FB = new Facebook([
            'app_id' => get_option("facebook_app_id", ""),
            'app_secret' => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_app_version", "v21.0"),
        ]);

        $query = DB::table('accounts')->whereIn('social_network', ['facebook', 'instagram']);

        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }

        $accounts = $query->orderBy('created', 'ASC')->get();

        if ($accounts->isEmpty()) {
            logger()->info("[Inbox] No accounts found for mentions sync");
            return;
        }

        foreach ($accounts as $account) {
            try {
                if ($account->social_network == 'facebook') {
                    // ✅ Only sync mentions for Pages (not profiles)
                    if ($account->category != 'page') {
                        logger()->info("[Inbox] Skipping mentions for {$account->category} account {$account->id} ('{$account->name}')");
                        continue;
                    }
                    
                    $pageId = $account->pid;
                    logger()->info("[Inbox] Fetching mentions for Facebook page: {$pageId}");
                    
                    $response = $FB->get(
                        "/{$pageId}/feed?fields=from,id,message,story,full_picture,created_time,permalink_url,message_tags,to&limit=100",
                        $account->token
                    )->getDecodedBody();
                    
                    if (empty($response['data'])) {
                        logger()->info("[Inbox] No feed posts found for account {$account->id}");
                        continue;
                    }
                    
                    $mentionCount = 0;
                    foreach ($response['data'] as $message) {
                        $isTagged = false;
                        
                        // Check message_tags
                        if (!empty($message['message_tags'])) {
                            foreach ($message['message_tags'] as $tag) {
                                if (is_array($tag)) {
                                    foreach ($tag as $t) {
                                        if (($t['id'] ?? '') == $pageId) {
                                            $isTagged = true;
                                            break 2;
                                        }
                                    }
                                } elseif (($tag['id'] ?? '') == $pageId) {
                                    $isTagged = true;
                                    break;
                                }
                            }
                        }
                        
                        // Check 'to' field
                        if (!empty($message['to']['data'])) {
                            foreach ($message['to']['data'] as $taggedEntity) {
                                if (($taggedEntity['id'] ?? '') == $pageId) {
                                    $isTagged = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($isTagged && ($message['from']['id'] ?? '') != $pageId) {
                            self::processFacebookMention($account, $message);
                            $mentionCount++;
                        }
                    }
                    
                    logger()->info("[Inbox] Processed {$mentionCount} mentions for account {$account->id}");
                    
                } else {
                    // Instagram tags (works for both profile and business accounts)
                    logger()->info("[Inbox] Fetching tags for Instagram account: {$account->pid}");
                    
                    $response = $FB->get(
                        "{$account->pid}/tags?fields=caption,permalink,username,id,media_url,timestamp&limit=50",
                        $account->token
                    )->getDecodedBody();
                    
                    if (empty($response['data'])) {
                        logger()->info("[Inbox] No tags found for Instagram account {$account->id}");
                        continue;
                    }
                    
                    $tagCount = 0;
                    foreach ($response['data'] as $message) {
                        self::processInstagramTag($account, $message);
                        $tagCount++;
                    }
                    
                    logger()->info("[Inbox] Processed {$tagCount} tags for account {$account->id}");
                }

            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                self::handleFacebookError($e, $account, 'mentions');
            } catch (\Exception $e) {
                logger()->error("[Inbox] Error getting mentions for account {$account->id}: " . $e->getMessage());
            }
        }
        
        logger()->info("[Inbox] Mentions sync completed");
        
    } catch (\Exception $e) {
        logger()->error("[Inbox] get_mentions error: " . $e->getMessage());
    }
}

/**
 * Get reviews from Facebook
 * ✅ Only syncs for Facebook Pages (category = 'page')
 */
public static function get_reviews($brandId = null)
{
    try {
        $FB = new Facebook([
            'app_id' => get_option("facebook_app_id", ""),
            'app_secret' => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_app_version", "v21.0"),
        ]);

        // ✅ Only get Page accounts (category = 'page')
        $query = DB::table('accounts')->where('social_network', 'facebook')
            ->where('category', 'page'); // Only Pages have reviews

        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }

        $accounts = $query->orderBy('created', 'ASC')->get();

        if ($accounts->isEmpty()) {
            logger()->info("[Inbox] No Facebook Page accounts found for reviews sync");
            return;
        }

        foreach ($accounts as $account) {
            try {
                $pageId = $account->pid;
                
                logger()->info("[Inbox] Fetching reviews for Facebook page {$account->id}: {$pageId}");
                
                $response = $FB->get(
                    "/{$pageId}/ratings?fields=reviewer,rating,created_time,recommendation_type,review_text,has_rating,has_review,open_graph_story&limit=100",
                    $account->token
                )->getDecodedBody();

                if (empty($response['data'])) {
                    logger()->info("[Inbox] No reviews found for account {$account->id}");
                    continue;
                }

                $reviewCount = 0;
                foreach ($response['data'] as $message) {
                    self::processFacebookReview($account, $message);
                    $reviewCount++;
                }
                
                logger()->info("[Inbox] ✓ Processed {$reviewCount} reviews for account {$account->id}");

            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                $errorCode = $e->getCode();
                
                // If still getting error 100, it might be a profile misclassified as page
                if ($errorCode == 100 && strpos($e->getMessage(), 'User') !== false) {
                    logger()->error("[Inbox] Account {$account->id} is marked as 'page' but token is for a profile/user");
                    logger()->error("[Inbox] Please update category to 'profile' for this account or re-authenticate");
                    
                    // Optionally auto-update the category
                    DB::table('accounts')
                        ->where('id', $account->id)
                        ->update(['category' => 'profile']);
                    
                    logger()->info("[Inbox] Updated account {$account->id} category to 'profile'");
                } else {
                    self::handleFacebookError($e, $account, 'reviews');
                }
            } catch (\Exception $e) {
                logger()->error("[Inbox] Error getting reviews for account {$account->id}: " . $e->getMessage());
            }
        }
        
        logger()->info("[Inbox] Reviews sync completed");
        
    } catch (\Exception $e) {
        logger()->error("[Inbox] get_reviews error: " . $e->getMessage());
    }
}

/**
 * Get ad comments from Facebook and Instagram
 */
public static function get_ad_comments($brandId = null)
{
    try {
        $FB = new Facebook([
            'app_id' => get_option("facebook_app_id", ""),
            'app_secret' => get_option("facebook_app_secret", ""),
            'default_graph_version' => get_option("facebook_app_version", "v21.0"),
        ]);

        $query = DB::table('accounts')->whereIn('social_network', ['facebook', 'instagram']);

        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }

        $accounts = $query->orderBy('created', 'ASC')->get();

        if ($accounts->isEmpty()) {
            logger()->info("[Inbox] No accounts found for ad comments sync");
            return;
        }

        foreach ($accounts as $account) {
            try {
                if ($account->social_network == 'facebook') {
                    // ✅ Only sync ad comments for Pages
                    if ($account->category != 'page') {
                        logger()->info("[Inbox] Skipping ad comments for {$account->category} account {$account->id}");
                        continue;
                    }
                    
                    $pageId = $account->pid;
                    logger()->info("[Inbox] Fetching ad comments for Facebook page: {$pageId}");
                    
                    $response = $FB->get(
                        "/{$pageId}/ads_posts?fields=permalink_url,comments{message,from,created_time,is_hidden,is_private,comment_count,comments}&limit=100",
                        $account->token
                    )->getDecodedBody();
                } else {
                    // Instagram (works for both profile and business)
                    logger()->info("[Inbox] Fetching ad comments for Instagram: {$account->pid}");
                    
                    $response = $FB->get(
                        "{$account->pid}/media?fields=id,permalink,comments_count,children{media_url},comments.limit(50){id,timestamp,from,username,text,comments,replies{from,username,text,id,timestamp}}&limit=100",
                        $account->token
                    )->getDecodedBody();
                }

                if (empty($response['data'])) {
                    logger()->info("[Inbox] No ad posts found for account {$account->id}");
                    continue;
                }

                $commentCount = 0;
                foreach ($response['data'] as $row) {
                    if (!empty($row['comments']['data'])) {
                        foreach ($row['comments']['data'] as $message) {
                            if ($account->social_network == 'facebook') {
                                self::processFacebookAdComment($account, $row, $message);
                            } else {
                                self::processInstagramAdComment($account, $row, $message);
                            }
                            $commentCount++;
                        }
                    }
                }
                
                logger()->info("[Inbox] Processed {$commentCount} ad comments for account {$account->id}");

            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                self::handleFacebookError($e, $account, 'ad_comments');
            } catch (\Exception $e) {
                logger()->error("[Inbox] Error getting ad comments for account {$account->id}: " . $e->getMessage());
            }
        }
        
        logger()->info("[Inbox] Ad comments sync completed");
        
    } catch (\Exception $e) {
        logger()->error("[Inbox] get_ad_comments error: " . $e->getMessage());
    }
}

/**
 * Handle Facebook API errors with detailed logging
 */
protected static function handleFacebookError($exception, $account, $syncType)
{
    $errorCode = $exception->getCode();
    $errorMessage = $exception->getMessage();
    $errorType = $exception->getErrorType();
    
    logger()->error("[Inbox] Facebook API error for account {$account->id} ({$syncType})");
    logger()->error("[Inbox] Category: {$account->category}");
    logger()->error("[Inbox] Error code: {$errorCode}");
    logger()->error("[Inbox] Error type: {$errorType}");
    logger()->error("[Inbox] Error message: {$errorMessage}");
    
    switch ($errorCode) {
        case 100:
            logger()->error("[Inbox] Invalid parameter or endpoint");
            if (strpos($errorMessage, 'User') !== false) {
                logger()->error("[Inbox] ⚠️  Account {$account->id} category is '{$account->category}' but token appears to be for a profile/user");
                logger()->error("[Inbox] Please check if category is correct or re-authenticate");
            }
            logger()->error("[Inbox] Checks:");
            logger()->error("[Inbox]   - Category in DB: {$account->category}");
            logger()->error("[Inbox]   - Page ID (pid): {$account->pid}");
            logger()->error("[Inbox]   - Required permissions: pages_read_engagement, pages_manage_engagement");
            break;
            
        case 190:
            logger()->error("[Inbox] Invalid or expired access token");
            logger()->error("[Inbox] ⚠️  ACTION REQUIRED: Re-authenticate account {$account->id}");
            break;
            
        case 200:
        case 210:
            logger()->error("[Inbox] Missing permissions");
            logger()->error("[Inbox] Required: pages_show_list, pages_read_engagement, pages_manage_posts");
            if ($syncType == 'reviews') {
                logger()->error("[Inbox]   + pages_manage_engagement");
            }
            break;
            
        case 104:
            logger()->error("[Inbox] Feature not available for this page");
            if ($syncType == 'reviews') {
                logger()->error("[Inbox] Reviews must be enabled in page settings");
            }
            break;
            
        case 803:
            logger()->error("[Inbox] Some pages could not be accessed - permissions issue");
            break;
            
        default:
            logger()->error("[Inbox] Unhandled error code: {$errorCode}");
    }
}
    /**
     * Process Facebook mention
     */
    protected static function processFacebookMention($account, $message)
    {
        // Set default if from is empty
        if (empty($message['from'])) {
            $message['from']['name'] = 'Facebook User';
            $message['from']['id'] = '1';
        }

        // Skip if message is from the account itself
        if (($message['from']['id'] ?? '') == $account->pid) {
            return;
        }

        $fromUserId = $message['from']['id'] ?? '';
        $toType = ((!empty($message['from']) && $message['from']['id'] != $account->pid) || empty($message['from'])) ? 'me' : '';
        $fromName = $message['from']['name'] ?? '';
        $createdTime = $message['created_time'] ?? null;

        // Set images
        if ($fromUserId == $account->pid) {
            $fromImage = get_file_url($account->avatar);
            $toImage = theme_public_asset('img/default.png');
        } else {
            $fromImage = theme_public_asset('img/default.png');
            $toImage = get_file_url($account->avatar);
        }

        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'facebook',
            'inbox_type' => 'Mentions',
            'post_id' => $message['id'],
            'post_url' => $message['permalink_url'] ?? '',
            'message' => $message['message'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $fromUserId,
            'message_id' => $message['id'],
            'to_type' => $toType,
            'to_name' => $account->name,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'to_user_id' => $account->pid,
            'created_time' => $createdTime,
        ];

        // Use raw SQL with ON DUPLICATE KEY UPDATE
        DB::statement("
            INSERT INTO inbox_comments 
            (user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time,media_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE message = VALUES(message), post_url = VALUES(post_url)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['post_url'], $data['brand_id'], $data['team_id'], $data['conversation_id'],
                $data['media_type'], $data['inbox_type'], $data['message'], $data['from_name'], $data['from_user_id'],
                $data['to_name'], $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], $data['message_id'],
                $data['created_time'], ''
            ]
        );
    }

    /**
     * Process Instagram tag
     */
    protected static function processInstagramTag($account, $message)
    {
        $fromUserId = $message['id'] ?? '';
        $toType = ((!empty($message['id']) && $message['id'] != $account->pid) || empty($message['id'])) ? 'me' : '';
        $fromName = $message['username'] ?? '';
        $createdTime = $message['timestamp'] ?? null;

        // Set images
        if ($fromUserId == $account->pid) {
            $fromImage = get_file_url($account->avatar);
            $toImage = theme_public_asset('img/default.png');
        } else {
            $fromImage = theme_public_asset('img/default.png');
            $toImage = get_file_url($account->avatar);
        }

        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'instagram',
            'inbox_type' => 'Tags',
            'post_id' => $message['id'],
            'post_url' => $message['permalink'] ?? '',
            'message' => $message['caption'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $fromUserId,
            'message_id' => $message['id'],
            'to_type' => $toType,
            'to_name' => $account->name,
            'to_user_id' => $account->pid,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'created_time' => $createdTime,
        ];

        // Use raw SQL with ON DUPLICATE KEY UPDATE
        DB::statement("
            INSERT INTO inbox_comments 
            (user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time,media_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE message = VALUES(message), post_url = VALUES(post_url)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['post_url'], $data['brand_id'], $data['team_id'], $data['conversation_id'],
                $data['media_type'], $data['inbox_type'], $data['message'], $data['from_name'], $data['from_user_id'],
                $data['to_name'], $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], $data['message_id'],
                $data['created_time'],''
            ]
        );
    }

   
    /**
     * Process Facebook review
     */
    protected static function processFacebookReview($account, $message)
    {
        $fromUserId = $message['reviewer']['id'] ?? '';
        $toType = ((!empty($message['reviewer']) && $message['reviewer']['id'] != $account->pid) || empty($message['reviewer'])) ? 'me' : '';
        $fromName = $message['reviewer']['name'] ?? '';
        $createdTime = $message['created_time'] ?? null;

        // Set images
        if ($fromUserId == $account->pid) {
            $fromImage = get_file_url($account->avatar);
            $toImage = theme_public_asset('img/default.png');
        } else {
            $fromImage = theme_public_asset('img/default.png');
            $toImage = get_file_url($account->avatar);
        }

        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'facebook',
            'inbox_type' => 'Review',
            'post_id' => $message['open_graph_story']['id'] ?? '',
            'message' => $message['review_text'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $fromUserId,
            'message_id' => $message['open_graph_story']['id'] ?? '',
            'to_type' => $toType,
            'to_name' => $account->name,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'to_user_id' => $account->pid,
            'created_time' => $createdTime,
        ];

        // Insert ignore (only insert if not exists)
        DB::statement("
            INSERT IGNORE INTO inbox_comments 
            (user_id, account_id, post_id, brand_id, team_id, conversation_id, media_type, inbox_type, message, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time,media_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['brand_id'], $data['team_id'], $data['conversation_id'],
                $data['media_type'], $data['inbox_type'], $data['message'], $data['from_name'], $data['from_user_id'],
                $data['to_name'], $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], $data['message_id'],
                $data['created_time'],''
            ]
        );
    }

   
    /**
     * Process Facebook ad comment
     */
    protected static function processFacebookAdComment($account, $row, $message)
    {
        // Skip hidden comments
        if ($message['is_hidden'] ?? false) {
            return;
        }

        $fromUserId = $message['from']['id'] ?? '';
        $toType = ((!empty($message['from']) && $message['from']['id'] != $account->pid) || empty($message['from'])) ? 'me' : '';
        $fromName = $message['from']['name'] ?? '';
        $createdTime = $message['created_time'] ?? null;

        // Set images
        if ($fromUserId == $account->pid) {
            $fromImage = get_file_url($account->avatar);
            $toImage = theme_public_asset('img/default.png');
        } else {
            $fromImage = theme_public_asset('img/default.png');
            $toImage = get_file_url($account->avatar);
        }

        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'facebook',
            'inbox_type' => 'AdComment',
            'post_id' => $row['id'],
            'post_url' => $row['permalink_url'] ?? '',
            'message' => $message['message'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $fromUserId,
            'message_id' => $message['id'],
            'to_type' => $toType,
            'to_name' => $account->name,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'to_user_id' => $account->pid,
            'created_time' => $createdTime,
        ];

        // Insert ignore parent comment  into inbox_comments table (not inbox)
        DB::statement("
            INSERT IGNORE INTO inbox_comments 
            (user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, media_url, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time, comment_count)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['post_url'], $data['brand_id'], $data['team_id'], $data['conversation_id'],
                $data['media_type'], $data['inbox_type'], $data['message'], '', $data['from_name'], $data['from_user_id'],
                $data['to_name'], $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], $data['message_id'],
                $data['created_time'], ($message['comment_count'] ?? 0)
            ]
        );

        // Process child comments (replies)
        if (($message['comment_count'] ?? 0) > 0 && !empty($message['comments']['data'])) {
            foreach ($message['comments']['data'] as $childMessage) {
                self::processFacebookAdCommentChild($account, $row, $message, $childMessage);
            }
        }
    }

    /**
     * Process Facebook ad comment child (reply)
     */
    protected static function processFacebookAdCommentChild($account, $row, $parentMessage, $childMessage)
    {
        $fromUserId = $childMessage['from']['id'] ?? '';
        $toType = ((!empty($childMessage['from']) && $childMessage['from']['id'] != $account->pid) || empty($childMessage['from'])) ? 'me' : '';
        $fromName = $childMessage['from']['name'] ?? '';

        // Set images
        if ($fromUserId == $account->pid) {
            $fromImage = get_file_url($account->avatar);
            $toImage = theme_public_asset('img/default.png');
        } else {
            $fromImage = theme_public_asset('img/default.png');
            $toImage = get_file_url($account->avatar);
        }

        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'facebook',
            'inbox_type' => 'AdComment',
            'post_id' => $row['id'],
            'post_url' => $row['permalink_url'] ?? '',
            'message' => $childMessage['message'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $fromUserId,
            'message_id' => $childMessage['id'],
            'to_type' => $toType,
            'to_name' => $account->name,
            'to_user_id' => $account->pid,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'created_time' => $childMessage['created_time'] ?? null,
            'is_child' => 1,
            'parent_id' => $parentMessage['id'],
        ];

        // Insert child comment into inbox_comments table
        DB::statement("
            INSERT IGNORE INTO inbox_comments 
            (user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, media_url, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time, is_child, parent_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['post_url'], $data['brand_id'], $data['team_id'], $data['conversation_id'],
                $data['media_type'], $data['inbox_type'], $data['message'], '', $data['from_name'], $data['from_user_id'],
                $data['to_name'], $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], $data['message_id'],
                $data['created_time'], $data['is_child'], $data['parent_id']
            ]
        );
    }

    /**
     * Process Instagram ad comment
     */
    protected static function processInstagramAdComment($account, $row, $message)
    {
        $fromUserId = $message['from']['id'] ?? '';
        $toType = ((!empty($message['from']) && $message['from']['id'] != $account->pid) || empty($message['from'])) ? 'me' : '';
        $fromName = $message['from']['username'] ?? '';
        $createdTime = $message['timestamp'] ?? null;

        // Set images
        if ($fromUserId == $account->pid) {
            $fromImage = get_file_url($account->avatar);
            $toImage = theme_public_asset('img/default.png');
        } else {
            $fromImage = theme_public_asset('img/default.png');
            $toImage = get_file_url($account->avatar);
        }

        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'instagram',
            'inbox_type' => 'AdComment',
            'post_id' => $row['id'],
            'post_url' => $row['permalink'] ?? '',
            'message' => $message['text'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $fromUserId,
            'message_id' => $message['id'],
            'to_type' => $toType,
            'to_name' => $account->name,
            'to_user_id' => $account->pid,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'created_time' => $createdTime,
        ];

        // Insert parent comment into inbox_comments table
        DB::statement("
            INSERT IGNORE INTO inbox_comments 
            (user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, media_url, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['post_url'], $data['brand_id'], $data['team_id'], $data['conversation_id'],
                $data['media_type'], $data['inbox_type'], $data['message'], '', $data['from_name'], $data['from_user_id'],
                $data['to_name'], $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], $data['message_id'],
                $data['created_time']
            ]
        );

        // Process replies
        if (!empty($message['replies']['data'])) {
            foreach ($message['replies']['data'] as $childMessage) {
                self::processInstagramAdCommentChild($account, $row, $message, $childMessage);
            }
        }
    }

    /**
     * Process Instagram ad comment child (reply)
     */
    protected static function processInstagramAdCommentChild($account, $row, $parentMessage, $childMessage)
    {
        $fromUserId = $childMessage['from']['id'] ?? '';
        $toType = ((!empty($childMessage['from']) && $childMessage['from']['id'] != $account->pid) || empty($childMessage['from'])) ? 'me' : '';
        $fromName = $childMessage['from']['username'] ?? '';

        // Set images
        if ($fromUserId == $account->pid) {
            $fromImage = get_file_url($account->avatar);
            $toImage = theme_public_asset('img/default.png');
        } else {
            $fromImage = theme_public_asset('img/default.png');
            $toImage = get_file_url($account->avatar);
        }

        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'instagram',
            'inbox_type' => 'AdComment',
            'post_id' => $row['id'],
            'post_url' => $row['permalink'] ?? '',
            'message' => $childMessage['text'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $fromUserId,
            'message_id' => $childMessage['id'],
            'to_type' => $toType,
            'to_name' => $account->name,
            'to_user_id' => $account->pid,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'created_time' => $childMessage['timestamp'] ?? null,
            'is_child' => 1,
            'parent_id' => $parentMessage['id'],
        ];

        // Insert child comment into inbox_comments table
        DB::statement("
            INSERT IGNORE INTO inbox_comments 
            (user_id, account_id, post_id, post_url, brand_id, team_id, conversation_id, media_type, inbox_type, message, media_url, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time, is_child, parent_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['post_url'], $data['brand_id'], $data['team_id'], $data['conversation_id'],
                $data['media_type'], $data['inbox_type'], $data['message'], '', $data['from_name'], $data['from_user_id'],
                $data['to_name'], $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], $data['message_id'],
                $data['created_time'], $data['is_child'], $data['parent_id']
            ]
        );
    }



	public static function get_linkedin_comments($brandId = null)
{
    try {
        logger()->info("[LinkedIn] Starting LinkedIn comments sync");
        
        // Get LinkedIn accounts
        $query = DB::table('accounts')->where('social_network', 'linkedin');

        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }
$query->where('id', 4);
        $accounts = $query->orderBy('created', 'DESC')->get();

        if ($accounts->isEmpty()) {
            logger()->info("[LinkedIn] No LinkedIn accounts found");
            return;
        }

        logger()->info("[LinkedIn] Found " . $accounts->count() . " LinkedIn account(s)");
//echo "<pre>";print_r($accounts);exit;
        foreach ($accounts as $account) {
            try {
                logger()->info("[LinkedIn] Processing account {$account->id}: {$account->name}");
                
                // Initialize LinkedIn API client
                $linkedin = new LinkedinAPI(
                    get_option("linkedin_app_id", ""),
                    get_option("linkedin_app_secret", ""),
                    "", // callback not needed for API calls
                    "", // scopes not needed for API calls
                    true // SSL verification
                );
                
                // Set type based on account category
                if ($account->category == 'page') {
                    $linkedin->setType('urn:li:organization:');
                } else {
                    $linkedin->setType('urn:li:person:');
                }

                // Get comment notifications
                $response1 = $linkedin->linkedInCommentGet($account->token, $account->pid);
                $notifications = json_decode($response1, true);
				
                if (empty($notifications['elements'])) {
                    logger()->info("[LinkedIn] No comment notifications for account {$account->id}");
                    continue;
                }

                logger()->info("[LinkedIn] Found " . count($notifications['elements']) . " notifications for account {$account->id}");
                
                $commentCount = 0;
                
                foreach ($notifications['elements'] as $notification) {
                    
                    $sourcePost = $notification['sourcePost'] ?? '';
                    
                    if (empty($sourcePost)) {
                        logger()->warning("[LinkedIn] No sourcePost in notification for account {$account->id}");
                        continue;
                    }

                    try {
                        // Get detailed comment information
                        $response2 = $linkedin->linkedInCommentDetailGet($account->token, $sourcePost);
                        $commentDetails = json_decode($response2, true);

                        if (empty($commentDetails['elements'])) {
                            logger()->debug("[LinkedIn] No comment details for post {$sourcePost}");
                            continue;
                        }

                        // Process each comment
                        foreach ($commentDetails['elements'] as $comment) {
                            self::processLinkedInComment($account, $sourcePost, $comment);
                            $commentCount++;
                        }
                        
                    } catch (\Exception $e) {
                        logger()->error("[LinkedIn] Error getting details for post {$sourcePost}: " . $e->getMessage());
                    }
                }
                
                logger()->info("[LinkedIn] ✓ Processed {$commentCount} comments for account {$account->id}");

            } catch (\Exception $e) {
                logger()->error("[LinkedIn] Error processing account {$account->id}: " . $e->getMessage());
                logger()->error("[LinkedIn] Stack trace: " . $e->getTraceAsString());
            }
        }
        
        logger()->info("[LinkedIn] LinkedIn comments sync completed");
        
    } catch (\Exception $e) {
        logger()->error("[LinkedIn] get_linkedin_comments error: " . $e->getMessage());
        logger()->error("[LinkedIn] Stack trace: " . $e->getTraceAsString());
    }
}

public static function getPostDetailLinkedin($postId, $token){
	$linkedin = new LinkedinAPI(
		get_option("linkedin_app_id", ""),
		get_option("linkedin_app_secret", ""),
		"", // callback not needed for API calls
		"", // scopes not needed for API calls
		true // SSL verification
	);
	$response2 = $linkedin->linkedInPostDetailGet($token, $postId);
    return $postDetails = json_decode($response2, true);
}

/**
 * Process a single LinkedIn comment and save to database
 * 
 * @param object $account The account object
 * @param string $sourcePost The post URN
 * @param array $comment The comment data
 * @return void
 */
protected static function processLinkedInComment($account, $sourcePost, $comment)
{
    try {
        $actor = $comment['actor'] ?? '';
        
        // Build account URNs
        $accountOrgUrn = 'urn:li:organization:' . $account->pid;
        $accountPersonUrn = 'urn:li:person:' . $account->pid;
        
        // Determine if comment is TO the account (not FROM the account)
        $toType = (!empty($actor) && $actor != $accountOrgUrn && $actor != $accountPersonUrn) ? 'me' : '';
        
        // Get commenter name
        if ($actor == $accountOrgUrn || $actor == $accountPersonUrn) {
            // Comment is from the account itself
            $fromName = $account->name;
        } else {
            // Comment is from someone else - try to get their name
            $firstName = $comment['actor~']['localizedFirstName'] ?? '';
            $lastName = $comment['actor~']['localizedLastName'] ?? '';
            
            if (!empty($firstName) || !empty($lastName)) {
                $fromName = trim($firstName . ' ' . $lastName);
            } else {
                $fromName = $actor ?: 'LinkedIn User';
            }
        }
        
        // Convert LinkedIn timestamp (milliseconds) to datetime
        $milliseconds = $comment['created']['time'] ?? 0;
        $seconds = ceil($milliseconds / 1000);
        $createdTime = date('Y-m-d H:i:s', $seconds);
        
        // Get profile images
        if ($actor == $accountOrgUrn || $actor == $accountPersonUrn) {
            // Comment from account itself
            $fromImage = get_file_url($account->avatar);
            $toImage = get_file_url($account->avatar);
        } else {
            // Comment from someone else
            $toImage = get_file_url($account->avatar);
            
            // Try to get commenter's profile picture
            $profilePicture = $comment['actor~']['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'] ?? '';
            
            if (!empty($profilePicture)) {
                $fromImage = $profilePicture;
            } else {
                $fromImage = theme_public_asset('img/default.png');
            }
        }
        
        // Prepare data for insertion
        $data = [
            'user_id' => 1,
            'account_id' => $account->id,
            'post_id' => $sourcePost,
            'brand_id' => $account->brand_id,
            'team_id' => $account->team_id,
            'conversation_id' => '',
            'media_type' => 'linkedin',
            'inbox_type' => 'Comment',
            'message' => $comment['message']['text'] ?? '',
            'from_name' => $fromName,
            'from_user_id' => $actor,
            'message_id' => $comment['id'] ?? '',
            'to_type' => $toType,
            'to_name' => $account->name,
            'from_image' => $fromImage,
            'to_image' => $toImage,
            'to_user_id' => $account->pid,
            'created_time' => $createdTime,
        ];

        // Insert into database (ignore duplicates)
        DB::statement("
            INSERT IGNORE INTO inbox_comments 
            (user_id, account_id, post_id, brand_id, team_id, conversation_id, media_type, inbox_type, message, from_name, from_user_id, to_name, to_type, to_user_id, from_image, to_image, message_id, created_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'], $data['account_id'], $data['post_id'], $data['brand_id'], 
                $data['team_id'], $data['conversation_id'], $data['media_type'], $data['inbox_type'], 
                $data['message'], $data['from_name'], $data['from_user_id'], $data['to_name'], 
                $data['to_type'], $data['to_user_id'], $data['from_image'], $data['to_image'], 
                $data['message_id'], $data['created_time']
            ]
        );
        
        logger()->debug("[LinkedIn] Saved comment from {$fromName} on post {$sourcePost}");
        
    } catch (\Exception $e) {
        logger()->error("[LinkedIn] Error processing comment: " . $e->getMessage());
        logger()->error("[LinkedIn] Comment data: " . json_encode($comment));
    }
}

/**
 * Post a reply to a LinkedIn comment
 * 
 * @param string $token Access token
 * @param string $postUrn Post URN
 * @param string $actorUrn Actor URN
 * @param string $commentText Comment text
 * @return array Status and message
 */
public static function postLinkedInComment($token, $postUrn, $actorUrn, $commentText,$completeId,$id)
{
    try {
        $linkedin = new LinkedinAPI(
            get_option("linkedin_app_id", ""),
            get_option("linkedin_app_secret", ""),
            "",
            "",
            true
        );
        
        $response = $linkedin->linkedInCommentPost($token, $postUrn, $actorUrn, $commentText);
        $result = json_decode($response, true);
        print_r($result);exit;
        if (isset($result['id'])) {
			if($completeId == '1' || $completeId == 1){
				DB::table('inbox_comments')->where('id', $id)->update(['is_completed' => 1]);
			}
            return ['status' => 'success', 'message' => 'Comment posted successfully'];
        }
        
        return ['status' => 'error', 'message' => $result['message'] ?? 'Failed to post comment'];
        
    } catch (\Exception $e) {
        logger()->error("[LinkedIn] postLinkedInComment error: " . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
}