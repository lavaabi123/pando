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
        Schema::create('inbox_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name', 500);
            $table->integer('added_user_id');
            $table->integer('brand_id');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('added_user_id');
            $table->index('brand_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_tags');
    }
};
