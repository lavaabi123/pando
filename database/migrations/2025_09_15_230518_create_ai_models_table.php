<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('id_secure', 50)
                  ->nullable()
                  ->unique();
            $table->string('provider');       // openai, claude, gemini, deepseek...
            $table->string('model_key');      // gpt-4o, gpt-5, claude-haiku...
            $table->string('name');           // Friendly name
            $table->string('category')->default('text'); 
            $table->string('type')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->string('api_type')->default('chat')
                  ->comment('API endpoint type: chat, responses, audio, image, video, embedding...');
            $table->json('api_params')->nullable()
                  ->comment('Custom API params mapping, e.g., {"max_tokens":"max_output_tokens"}');
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'model_key', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
