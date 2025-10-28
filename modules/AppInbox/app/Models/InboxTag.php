<?php

namespace Modules\AppInbox\Models;

use Illuminate\Database\Eloquent\Model;

class InboxTag extends Model
{
    protected $table = 'inbox_tags';
    
    public $timestamps = false;

    protected $fillable = [
        'tag_name',
        'added_user_id',
        'brand_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
}
