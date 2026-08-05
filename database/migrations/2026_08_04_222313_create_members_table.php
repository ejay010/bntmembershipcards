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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('membership_number')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('tier');
            $table->string('status')->default('active');
            $table->date('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('apple_wallet_downloaded_at')->nullable();
            $table->timestamp('google_wallet_downloaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
