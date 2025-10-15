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
        Schema::table('credit_usages', function (Blueprint $table) {
            $table->dropUnique('credit_usages_team_id_date_unique'); 
            $table->unique(['team_id', 'feature', 'model', 'date'], 'credit_usages_team_feature_model_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_usages', function (Blueprint $table) {
            $table->dropUnique('credit_usages_team_feature_model_date_unique');
            $table->unique(['team_id', 'date'], 'credit_usages_team_id_date_unique');
        });
    }
};
