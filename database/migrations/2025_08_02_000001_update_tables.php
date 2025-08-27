<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('post_stats', 'method')) {
            Schema::table('post_stats', function (Blueprint $table) {
                $table->string('method', 15)->nullable()->after('campaign');
            });
        }

        if (!Schema::hasColumn('post_stats', 'query_id')) {
            Schema::table('post_stats', function (Blueprint $table) {
                $table->integer('query_id')->nullable()->after('method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('post_stats', 'method')) {
            Schema::table('post_stats', function (Blueprint $table) {
                $table->dropColumn('method');
            });
        }

        if (Schema::hasColumn('post_stats', 'query_id')) {
            Schema::table('post_stats', function (Blueprint $table) {
                $table->dropColumn('query_id');
            });
        }
    }
};