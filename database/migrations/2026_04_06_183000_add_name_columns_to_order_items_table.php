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
        Schema::table('order_items', function (Blueprint $blueprint) {
            $blueprint->string('product_name')->after('product_variant_id')->nullable();
            $blueprint->string('variant_name')->after('product_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['product_name', 'variant_name']);
        });
    }
};
