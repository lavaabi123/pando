<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

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
