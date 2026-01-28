<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ContentCalendarItem extends Model
{
    protected $fillable = [
        'content_plan_id',
        'user_id',
        'title',
        'content',
        'platform_versions',
        'content_type',
        'suggested_hashtags',
        'suggested_post_time',
        'topic_category',
        'ai_metadata',
        'status'
    ];

    protected $casts = [
        'platform_versions' => 'array',
        'suggested_hashtags' => 'array',
        'suggested_post_time' => 'datetime',
        'ai_metadata' => 'array'
    ];

    public function contentPlan(): BelongsTo
    {
        return $this->belongsTo(ContentPlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
