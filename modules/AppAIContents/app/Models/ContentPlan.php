<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

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
