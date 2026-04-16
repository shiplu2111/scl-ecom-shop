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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_full_name')->nullable()->after('billing_address_id');
            $table->string('shipping_phone')->nullable()->after('shipping_full_name');
            $table->string('shipping_email')->nullable()->after('shipping_phone');
            $table->text('shipping_address_line')->nullable()->after('shipping_email');
            $table->string('shipping_postal_code')->nullable()->after('shipping_address_line');
            $table->unsignedBigInteger('shipping_division_id')->nullable()->after('shipping_postal_code');
            $table->unsignedBigInteger('shipping_district_id')->nullable()->after('shipping_division_id');
            $table->unsignedBigInteger('shipping_thana_id')->nullable()->after('shipping_district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_full_name',
                'shipping_phone',
                'shipping_email',
                'shipping_address_line',
                'shipping_postal_code',
                'shipping_division_id',
                'shipping_district_id',
                'shipping_thana_id'
            ]);
        });
    }
};
