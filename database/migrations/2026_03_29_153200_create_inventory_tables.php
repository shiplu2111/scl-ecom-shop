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
        Schema::create('inventories', function (Blueprint $row) {
            $row->id();
            $row->foreignId('product_id')->unique()->constrained()->onDelete('cascade');
            $row->integer('quantity')->default(0);
            $row->integer('low_stock_alert')->nullable();
            $row->timestamps();
            
            // Index for stock queries
            $row->index('quantity');
        });

        Schema::create('inventory_transactions', function (Blueprint $row) {
            $row->id();
            $row->foreignId('product_id')->constrained()->onDelete('cascade');
            $row->enum('type', ['IN', 'OUT', 'ADJUSTMENT']);
            $row->integer('quantity');
            $row->string('reference')->nullable();
            $row->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null'); // Linking to Admins directly for inventory audits
            $row->timestamps();
            
            // Indexes for transaction history and audits
            $row->index('type');
            $row->index(['product_id', 'type']);
            $row->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventories');
    }
};
