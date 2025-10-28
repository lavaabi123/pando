<?php

namespace Modules\Inbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
}
