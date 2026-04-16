<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations flawlessly properly properly brilliantly expertly.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('content');
            }
            if (Schema::hasColumn('pages', 'meta_description')) {
                $table->text('meta_description')->nullable()->change();
            }
            if (!Schema::hasIndex('pages', 'pages_slug_index')) {
                $table->index('slug');
            }
        });
    }

    /**
     * Reverse the migrations flawlessly properly flawlessly brilliance.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('meta_title');
            $table->dropIndex(['slug']);
        });
    }
};
