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
        Schema::create('inbox', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('account_id');
            $table->string('post_id', 200)->default('');
            $table->string('conversation_id', 50);
            $table->string('media_type', 100);
            $table->string('inbox_type', 200);
            $table->text('message')->charset('utf8mb4')->collation('utf8mb4_bin');
            $table->string('from_name', 255);
            $table->string('to_name', 255);
            $table->string('to_user_id', 100);
            $table->string('to_type', 10);
            $table->string('from_user_id', 100);
            $table->text('from_image');
            $table->text('to_image');
            $table->string('message_id', 500)->unique();
            $table->string('created_time', 100);
            $table->integer('brand_id');
            $table->string('team_id', 100);
            $table->integer('is_completed')->default(0);
            $table->integer('is_sent')->default(0);
            $table->integer('is_deleted')->default(0);
            $table->integer('is_favourite')->default(0);
            $table->integer('last_reviewed_user_id')->default(0);
            $table->timestamp('last_reviewed_date')->useCurrent()->useCurrentOnUpdate();
            $table->text('story')->default('');
            $table->text('attachments')->default('');
            $table->text('shares')->default('');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('account_id');
            $table->index('conversation_id');
            $table->index('brand_id');
            $table->index('is_completed');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox');
    }
};
