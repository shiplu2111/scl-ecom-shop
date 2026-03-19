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
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['address_line_1', 'address_line_2', 'city', 'state', 'country']);
            
            $table->foreignId('division_id')->after('user_id')->nullable()->constrained();
            $table->foreignId('district_id')->after('division_id')->nullable()->constrained();
            $table->foreignId('thana_id')->after('district_id')->nullable()->constrained();
            $table->string('address_line')->after('thana_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            
            $table->dropForeign(['division_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['thana_id']);
            $table->dropColumn(['division_id', 'district_id', 'thana_id', 'address_line']);
        });
    }
};
