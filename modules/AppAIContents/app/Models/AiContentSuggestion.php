<?php

namespace Modules\AppAIContents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

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
