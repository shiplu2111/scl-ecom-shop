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
        Schema::create('courier_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Stadefast, Pathao
            $table->enum('environment', ['sandbox', 'live'])->default('sandbox');
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('account_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_credentials');
    }
};
