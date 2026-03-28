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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('buying_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('buying_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('buying_price', 10, 2)->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('buying_price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('buying_price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('buying_price');
        });
    }
};
