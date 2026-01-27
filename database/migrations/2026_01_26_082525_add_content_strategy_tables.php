<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Content Plans - Master planning table
        Schema::create('content_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('business_goals')->nullable();
            $table->json('target_audience')->nullable();
            $table->json('platforms')->nullable(); // ['facebook', 'instagram', 'linkedin', etc]
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->timestamps();
        });

        // AI Generated Content Calendar
        Schema::create('content_calendar_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->json('platform_versions')->nullable(); // Platform-specific adaptations
            $table->string('content_type'); // post, story, reel, article, etc
            $table->json('suggested_hashtags')->nullable();
            $table->dateTime('suggested_post_time');
            $table->string('topic_category')->nullable();
            $table->json('ai_metadata')->nullable(); // AI reasoning, confidence scores
            $table->enum('status', ['suggested', 'approved', 'scheduled', 'posted', 'rejected'])->default('suggested');
            $table->timestamps();
        });

        // Topic Research & Trends
        Schema::create('topic_research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->string('topic');
            $table->text('research_summary');
            $table->json('trending_keywords')->nullable();
            $table->json('related_topics')->nullable();
            $table->json('search_volume_data')->nullable();
            $table->json('seasonal_trends')->nullable();
            $table->integer('relevance_score')->nullable(); // 1-100
            $table->json('sources')->nullable();
            $table->timestamp('researched_at');
            $table->timestamps();
        });

        // Competitor Analysis
        Schema::create('competitor_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('competitor_name');
            $table->string('competitor_url')->nullable();
            $table->json('competitor_accounts')->nullable(); // Social media handles
            $table->json('content_themes')->nullable(); // Topics they cover
            $table->json('posting_frequency')->nullable(); // Posts per platform per week
            $table->json('engagement_metrics')->nullable(); // Avg likes, comments, shares
            $table->json('content_types')->nullable(); // Types of content they post
            $table->json('best_performing_content')->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->json('content_gaps')->nullable(); // What they're missing
            $table->json('ai_insights')->nullable();
            $table->timestamp('analyzed_at');
            $table->timestamps();
        });

        // Content Gap Analysis
        Schema::create('content_gap_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->string('gap_category'); // topic, format, platform, timing
            $table->text('gap_description');
            $table->json('competitor_doing_well')->nullable(); // Who's doing this well
            $table->json('recommendations')->nullable();
            $table->integer('priority_score')->nullable(); // 1-100
            $table->integer('opportunity_score')->nullable(); // 1-100
            $table->enum('status', ['identified', 'planned', 'addressed', 'monitoring'])->default('identified');
            $table->timestamp('identified_at');
            $table->timestamps();
        });

        // Posting Time Analysis
        Schema::create('posting_time_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // facebook, instagram, linkedin, etc
            $table->string('account_id')->nullable();
            $table->json('best_times')->nullable(); // [{day: 'Monday', time: '09:00', score: 95}]
            $table->json('worst_times')->nullable();
            $table->json('engagement_by_hour')->nullable();
            $table->json('engagement_by_day')->nullable();
            $table->json('audience_activity_patterns')->nullable();
            $table->json('ai_recommendations')->nullable();
            $table->integer('data_points_analyzed')->nullable();
            $table->timestamp('analyzed_at');
            $table->timestamps();
        });

        // Hashtag Research
        Schema::create('hashtag_research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('hashtag');
            $table->string('platform'); // Platform-specific hashtag data
            $table->bigInteger('post_count')->nullable();
            $table->string('popularity_level')->nullable(); // low, medium, high, very_high
            $table->json('related_hashtags')->nullable();
            $table->decimal('engagement_rate', 5, 2)->nullable();
            $table->json('trending_data')->nullable();
            $table->boolean('is_branded')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->integer('relevance_score')->nullable(); // 1-100
            $table->text('usage_recommendation')->nullable();
            $table->timestamp('researched_at');
            $table->timestamps();
            
            $table->index(['platform', 'hashtag']);
            $table->index('is_trending');
        });

        // AI Content Suggestions History
        Schema::create('ai_content_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->text('prompt_used');
            $table->json('parameters')->nullable(); // Business goals, audience, etc
            $table->longText('ai_response');
            $table->json('suggestions')->nullable(); // Parsed suggestions
            $table->string('suggestion_type'); // calendar, topics, hashtags, competitor_insights
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 8, 4)->nullable();
            $table->enum('feedback', ['helpful', 'not_helpful', 'partially_helpful'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_content_suggestions');
        Schema::dropIfExists('hashtag_research');
        Schema::dropIfExists('posting_time_analyses');
        Schema::dropIfExists('content_gap_analyses');
        Schema::dropIfExists('competitor_analyses');
        Schema::dropIfExists('topic_research');
        Schema::dropIfExists('content_calendar_items');
        Schema::dropIfExists('content_plans');
    }
};