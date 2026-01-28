<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TopicResearch extends Model
{
    protected $fillable = [
        'user_id',
        'content_plan_id',
        'topic',
        'research_summary',
        'trending_keywords',
        'related_topics',
        'search_volume_data',
        'seasonal_trends',
        'relevance_score',
        'sources',
        'researched_at'
    ];

    protected $casts = [
        'trending_keywords' => 'array',
        'related_topics' => 'array',
        'search_volume_data' => 'array',
        'seasonal_trends' => 'array',
        'sources' => 'array',
        'researched_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contentPlan(): BelongsTo
    {
        return $this->belongsTo(ContentPlan::class);
    }
}
