<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group Admin
 */
class BlogController extends Controller
{
    // === CATEGORIES === //
    public function getCategories()
    {
        return response()->json(BlogCategory::all());
    }

    public function createCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = BlogCategory::create([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'status' => $request->status ?? true
        ]);
        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);
        $category->update($request->only(['name', 'slug', 'status']));
        return response()->json($category);
    }

    public function deleteCategory($id)
    {
        BlogCategory::findOrFail($id)->delete();
        return response()->json(['message' => 'Category deleted']);
    }

    // === TAGS === //
    public function getTags()
    {
        return response()->json(BlogTag::all());
    }

    public function createTag(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $tag = BlogTag::create([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name)
        ]);
        return response()->json($tag, 201);
    }

    public function updateTag(Request $request, $id)
    {
        $tag = BlogTag::findOrFail($id);
        $tag->update($request->only(['name', 'slug']));
        return response()->json($tag);
    }

    public function deleteTag($id)
    {
        BlogTag::findOrFail($id)->delete();
        return response()->json(['message' => 'Tag deleted']);
    }

    // === POSTS === //
    public function getPosts()
    {
        return response()->json(BlogPost::with(['category', 'tags', 'author'])->paginate(15));
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id'
        ]);

        $post = BlogPost::create([
            'title' => $request->title,
            'slug' => $request->slug ?? Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'author_id' => auth()->id(),
            'blog_category_id' => $request->blog_category_id,
            'status' => $request->status ?? 'published',
            'image_path' => $request->image_path
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load(['category', 'tags', 'author']), 201);
    }

    public function updatePost(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);
        $post->update($request->except(['tags']));

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load(['category', 'tags', 'author']));
    }

    public function deletePost($id)
    {
        BlogPost::findOrFail($id)->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}
