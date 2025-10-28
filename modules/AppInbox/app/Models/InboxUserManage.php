<?php

namespace Modules\AppInbox\Models;

use Illuminate\Database\Eloquent\Model;

class InboxUserManage extends Model
{
    protected $table = 'inbox_users_manage';
    
    public $timestamps = false;

    protected $fillable = [
        'inbox_id',
        'user_ids',
        'table_name',
        'added_user_id',
        'brand_id',
    ];

    protected $casts = [
        'created' => 'datetime',
    ];

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    /**
     * Get user ids as array
     */
    public function getUserIdsArrayAttribute()
    {
        return $this->user_ids ? explode(',', $this->user_ids) : [];
    }
}
