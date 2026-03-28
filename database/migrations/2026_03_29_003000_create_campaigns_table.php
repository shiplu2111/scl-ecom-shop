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
        Schema::create('campaigns', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('subject');
            $blueprint->longText('content');
            $blueprint->timestamp('sent_at')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('campaign_subscriber', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('subscriber_id')->constrained()->onDelete('cascade');
            $blueprint->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $blueprint->text('error')->nullable();
            $blueprint->timestamp('sent_at')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_subscriber');
        Schema::dropIfExists('campaigns');
    }
};
