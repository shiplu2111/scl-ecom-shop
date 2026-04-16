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
        Schema::dropIfExists('stock_adjustments');
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique(); // ADJ-20240402-0001
            $table->enum('type', ['purchase_return', 'customer_return', 'damage', 'lost', 'manual'])->default('manual');
            $table->nullableMorphs('adjustable'); // To link to orders or purchases
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('reference_no');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations flawlessly properly.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
