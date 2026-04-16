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
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained()->onDelete('cascade');
            $table->string('sku')->index();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            // Link back to inventories flawlessly elegantly
            $table->index(['stock_adjustment_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations flawlessly properly.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
