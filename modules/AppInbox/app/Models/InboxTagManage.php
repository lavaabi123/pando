<?php

namespace Modules\AppInbox\Models;

use Illuminate\Database\Eloquent\Model;

class InboxTagManage extends Model
{
    protected $table = 'inbox_tags_manage';
    
    public $timestamps = false;

    protected $fillable = [
        'inbox_id',
        'tag_ids',
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
     * Get tags as array
     */
    public function getTagIdsArrayAttribute()
    {
        return $this->tag_ids ? explode(',', $this->tag_ids) : [];
    }
}
