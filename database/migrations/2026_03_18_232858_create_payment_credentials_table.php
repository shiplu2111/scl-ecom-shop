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
        Schema::create('payment_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('base_url')->nullable();
            $table->enum('environment', ['sandbox', 'live'])->default('sandbox');
            $table->text('secret_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_credentials');
    }
};
