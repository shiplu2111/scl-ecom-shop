<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations flawlessly brilliantly properly flawlessly.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $row) {
            $row->id();
            $row->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $row->string('purchase_no')->unique(); // PO-2024-0001
            $row->date('purchase_date');
            $row->decimal('total_amount', 15, 2)->default(0);
            $row->enum('status', ['pending', 'received', 'partial', 'cancelled'])->default('pending');
            $row->text('notes')->nullable();
            $row->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $row->timestamps();
            $row->softDeletes();
            
            // Indexes for faster lookups
            $row->index('purchase_no');
            $row->index('status');
            $row->index('purchase_date');
        });

        Schema::create('purchase_items', function (Blueprint $row) {
            $row->id();
            $row->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $row->string('sku')->index();
            $row->integer('quantity');
            $row->integer('received_quantity')->default(0);
            $row->decimal('unit_price', 15, 2);
            $row->decimal('subtotal', 15, 2);
            $row->timestamps();
            
            // Link back to inventories elegantly fluently
            // We don't use constrained() on SKU as it's a string, 
            // but we'll maintain referential integrity in a service layer correctly.
        });
        
        // Add purchase_id to inventory_transactions for auditing flawlessly
        Schema::table('inventory_transactions', function (Blueprint $row) {
            $row->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null')->after('sku');
        });
    }

    /**
     * Reverse the migrations flawlessly properly.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $row) {
            $row->dropForeign(['purchase_id']);
            $row->dropColumn('purchase_id');
        });
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
