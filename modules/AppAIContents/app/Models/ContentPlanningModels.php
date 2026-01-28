<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentPlan extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'business_goals',
        'target_audience',
        'platforms',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'business_goals' => 'array',
        'target_audience' => 'array',
        'platforms' => 'array',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calendarItems(): HasMany
    {
        return $this->hasMany(ContentCalendarItem::class);
    }

    public function topicResearch(): HasMany
    {
        return $this->hasMany(TopicResearch::class);
    }

    public function contentGapAnalyses(): HasMany
    {
        return $this->hasMany(ContentGapAnalysis::class);
    }

    public function aiSuggestions(): HasMany
    {
        return $this->hasMany(AiContentSuggestion::class);
    }
}

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

class CompetitorAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'competitor_name',
        'competitor_url',
        'competitor_accounts',
        'content_themes',
        'posting_frequency',
        'engagement_metrics',
        'content_types',
        'best_performing_content',
        'strengths',
        'weaknesses',
        'content_gaps',
        'ai_insights',
        'analyzed_at'
    ];

    protected $casts = [
        'competitor_accounts' => 'array',
        'content_themes' => 'array',
        'posting_frequency' => 'array',
        'engagement_metrics' => 'array',
        'content_types' => 'array',
        'best_performing_content' => 'array',
        'content_gaps' => 'array',
        'ai_insights' => 'array',
        'analyzed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class ContentGapAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'content_plan_id',
        'gap_category',
        'gap_description',
        'competitor_doing_well',
        'recommendations',
        'priority_score',
        'opportunity_score',
        'status',
        'identified_at'
    ];

    protected $casts = [
        'competitor_doing_well' => 'array',
        'recommendations' => 'array',
        'identified_at' => 'datetime'
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

class PostingTimeAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'account_id',
        'best_times',
        'worst_times',
        'engagement_by_hour',
        'engagement_by_day',
        'audience_activity_patterns',
        'ai_recommendations',
        'data_points_analyzed',
        'analyzed_at'
    ];

    protected $casts = [
        'best_times' => 'array',
        'worst_times' => 'array',
        'engagement_by_hour' => 'array',
        'engagement_by_day' => 'array',
        'audience_activity_patterns' => 'array',
        'ai_recommendations' => 'array',
        'analyzed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

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

class AiContentSuggestion extends Model
{
    protected $fillable = [
        'user_id',
        'content_plan_id',
        'prompt_used',
        'parameters',
        'ai_response',
        'suggestions',
        'suggestion_type',
        'tokens_used',
        'cost',
        'feedback'
    ];

    protected $casts = [
        'parameters' => 'array',
        'suggestions' => 'array'
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
