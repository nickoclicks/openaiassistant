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
       Schema::create('api_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
        $table->integer('prompt_tokens');
        $table->integer('completion_tokens');
        $table->integer('total_tokens');
        $table->integer('response_time_ms');
        $table->timestamp('created_at')->useCurrent();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
