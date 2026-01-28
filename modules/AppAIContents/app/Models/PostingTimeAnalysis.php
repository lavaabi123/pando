<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

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
