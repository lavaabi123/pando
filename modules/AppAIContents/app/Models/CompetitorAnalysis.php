<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

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
