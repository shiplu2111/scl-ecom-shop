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
        Schema::create('suppliers', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('company_name')->nullable();
            $row->string('phone');
            $row->string('email')->nullable();
            $row->text('address');
            $row->enum('status', ['active', 'inactive'])->default('active');
            $row->timestamps();
            
            // Indexes for faster lookups
            $row->index('name');
            $row->index('company_name');
            $row->index('status');
        });

        Schema::create('product_supplier', function (Blueprint $row) {
            $row->id();
            $row->foreignId('product_id')->constrained()->onDelete('cascade');
            $row->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $row->decimal('purchase_price', 15, 2);
            $row->timestamp('last_supplied_at')->nullable();
            $row->timestamps();
            
            // Unique constraint to prevent duplicate links
            $row->unique(['product_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
        Schema::dropIfExists('suppliers');
    }
};
