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
        Schema::table('transactions', function (Blueprint $table) {
            // Add new columns flawlessly properly brilliantly
            $table->string('type')->default('product')->after('amount'); // product, delivery
            $table->string('description')->nullable()->after('type');
            $table->foreignId('user_id')->nullable()->after('order_id')->constrained()->onDelete('set null');
            
            // Update gateway enum (since we can't easily change enum in MySQL via Migration without DB::statement)
            // We'll use a string for more flexibility or just add it if possible
        });

        // Use DB statement to update enum if needed, or just change it to string for scaling
        \DB::statement("ALTER TABLE transactions MODIFY COLUMN gateway ENUM('stripe', 'uddoktapay', 'cod') DEFAULT 'stripe'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['type', 'description', 'user_id']);
        });

        \DB::statement("ALTER TABLE transactions MODIFY COLUMN gateway ENUM('stripe', 'uddoktapay') DEFAULT 'stripe'");
    }
};
