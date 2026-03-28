<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cleanup duplicates: Keep only the latest updated record for each gateway name
        $allowedGateways = ['UddoktaPay', 'SSLCommerz', 'Stripe', 'PayPal'];

        // Delete records not in allowed list
        DB::table('payment_credentials')
            ->whereNotIn('name', $allowedGateways)
            ->delete();

        // Delete duplicates within allowed list
        $duplicates = DB::table('payment_credentials')
            ->select('name', DB::raw('MAX(id) as latest_id'))
            ->whereIn('name', $allowedGateways)
            ->groupBy('name')
            ->get();

        $latestIds = $duplicates->pluck('latest_id')->toArray();

        // Delete records that are NOT the latest for their name
        if (!empty($latestIds)) {
            DB::table('payment_credentials')
                ->whereNotIn('id', $latestIds)
                ->delete();
        }

        // 2. Add Unique constraint
        Schema::table('payment_credentials', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_credentials', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
