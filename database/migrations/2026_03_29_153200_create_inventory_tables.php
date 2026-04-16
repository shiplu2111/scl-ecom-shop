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
            $row->string('sku')->unique();
            $row->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $row->decimal('buying_price', 15, 2)->nullable();
            $row->integer('quantity')->default(0);
            $row->integer('alert_quantity')->default(5);
            $row->timestamps();
            
            // Index for stock queries
            $row->index('quantity');
        });

        Schema::create('inventory_transactions', function (Blueprint $row) {
            $row->id();
            $row->string('sku')->index();
            $row->enum('type', ['IN', 'OUT', 'ADJUSTMENT']);
            $row->integer('quantity');
            $row->string('reference')->nullable();
            $row->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null'); // Linking to Admins directly for inventory audits
            $row->timestamps();
            
            // Indexes for transaction history and audits
            $row->index('type');
            $row->index(['sku', 'type']);
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
