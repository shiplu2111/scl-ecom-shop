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

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
