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
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('full_name')->after('user_id')->nullable();
            $table->string('phone')->after('full_name')->nullable();
            $table->string('label')->after('phone')->nullable(); // e.g., 'Home', 'Office'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'phone', 'label']);
        });
    }
};
