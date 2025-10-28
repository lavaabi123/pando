<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inbox_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('account_id');
            $table->string('post_id', 200);
            $table->mediumText('post_url');
            $table->string('conversation_id', 50)->default('');
            $table->string('media_type', 100);
            $table->string('inbox_type', 200);
            $table->text('message');
            $table->mediumText('media_url');
            $table->string('from_name', 255);
            $table->string('to_name', 255);
            $table->string('to_user_id', 100);
            $table->string('to_type', 10);
            $table->string('from_user_id', 100);
            $table->mediumText('from_image');
            $table->mediumText('to_image');
            $table->string('message_id', 500)->unique();
            $table->string('created_time', 100);
            $table->integer('brand_id');
            $table->string('team_id', 100);
            $table->integer('is_completed')->default(0);
            $table->integer('is_sent')->default(0);
            $table->integer('is_child')->default(0);
            $table->string('parent_id', 200)->default('');
            $table->integer('comment_count')->default(0);
            $table->integer('is_deleted')->default(0);
            $table->integer('is_favourite')->default(0);
            $table->integer('last_reviewed_user_id')->default(0);
            $table->timestamp('last_reviewed_date')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('account_id');
            $table->index('post_id');
            $table->index('brand_id');
            $table->index('is_completed');
            $table->index('is_deleted');
            $table->index('is_child');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_comments');
    }
};
