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
        Schema::table('payment_credentials', function (Blueprint $table) {
            $table->dropColumn(['success_url', 'cancel_url', 'callback_url', 'merchant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_credentials', function (Blueprint $table) {
            $table->string('merchant_id')->nullable()->after('environment');
            $table->string('callback_url')->nullable()->after('secret_key');
            $table->string('success_url')->nullable()->after('callback_url');
            $table->string('cancel_url')->nullable()->after('success_url');
        });
    }
};
