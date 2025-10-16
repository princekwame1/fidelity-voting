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
        Schema::create('vote_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('device_hash')->nullable();
            $table->string('ip_address')->nullable();
            $table->datetime('used_at')->nullable();
            $table->datetime('first_opened_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_tokens');
    }
};
