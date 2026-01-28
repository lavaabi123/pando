<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class HashtagResearch extends Model
{
    protected $fillable = [
        'user_id',
        'hashtag',
        'platform',
        'post_count',
        'popularity_level',
        'related_hashtags',
        'engagement_rate',
        'trending_data',
        'is_branded',
        'is_trending',
        'relevance_score',
        'usage_recommendation',
        'researched_at'
    ];

    protected $casts = [
        'related_hashtags' => 'array',
        'trending_data' => 'array',
        'is_branded' => 'boolean',
        'is_trending' => 'boolean',
        'researched_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
