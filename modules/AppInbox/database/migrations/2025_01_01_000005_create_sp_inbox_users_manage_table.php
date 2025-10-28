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
        Schema::create('inbox_users_manage', function (Blueprint $table) {
            $table->id();
            $table->integer('inbox_id');
            $table->string('user_ids', 500);
            $table->string('table_name', 200);
            $table->integer('added_user_id');
            $table->integer('brand_id');
            $table->timestamp('created')->useCurrent();
            
            $table->unique(['inbox_id', 'table_name']);
            $table->index('inbox_id');
            $table->index('table_name');
            $table->index('brand_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_users_manage');
    }
};
