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
        Schema::create('voter_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('voting_session_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('device_hash');
            $table->ipAddress('ip_address');
            $table->timestamps();

            // Ensure one email per device per event
            $table->unique(['event_id', 'device_hash']);
            // Index for faster lookups
            $table->index(['event_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voter_emails');
    }
};
