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
        // Pivot table for many-to-many relationship
        Schema::create('blog_category_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->foreignId('blog_category_id')->constrained('blog_categories')->onDelete('cascade');
            $table->timestamps();
        });

        // Migrate existing category from blog_posts to pivot table (if any)
        $posts = DB::table('blog_posts')->whereNotNull('blog_category_id')->get();
        foreach ($posts as $post) {
            DB::table('blog_category_post')->insert([
                'blog_post_id' => $post->id,
                'blog_category_id' => $post->blog_category_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Remove the single category column
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['blog_category_id']);
            $table->dropColumn('blog_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
        });

        Schema::dropIfExists('blog_category_post');
    }
};
