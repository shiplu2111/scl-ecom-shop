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
            $table->enum('payment_status', ['pending', 'advanced_paid', 'paid', 'failed', 'free'])->default('pending')->change();
            $table->boolean('delivery_charge_paid')->default(false)->after('coupon_id');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('delivery_charge_paid');
            $table->decimal('due_amount', 10, 2)->default(0)->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_charge_paid', 'paid_amount', 'due_amount']);
        });
    }
};
