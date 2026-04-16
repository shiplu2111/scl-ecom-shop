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
            $table->text('merchant_id')->nullable()->change();
            $table->text('secret_key')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_credentials', function (Blueprint $table) {
            $table->string('merchant_id')->nullable()->change();
            $table->string('secret_key')->nullable()->change();
        });
    }
};
