<?php

namespace Modules\AppInbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use JanuSoftware\Facebook\Facebook;

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
				if (!in_array($key, ['(i.created_time - INTERVAL 7 HOUR) >=', '(i.created_time - INTERVAL 7 HOUR) <='])) {
					$key = (strpos($key, 't.') !== false) ? $key : 'i.' . $key;
				}
				$query->where($key, $value);
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
	public static function postComment($token, $comment, $conversationId, $completeId, $endpoint)
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
					DB::table('inbox_comments')->where('conversation_id', $conversationId)->update(['is_completed' => 1]);
				}			
				return ['status' => 'success', 'message' => 'Comment posted'];
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
}
