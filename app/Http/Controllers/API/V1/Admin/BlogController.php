<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * @group Admin
 * @subgroup Blog Management
 */
class BlogController extends Controller
{
    // === CATEGORIES === //
    public function getCategories()
    {
        return response()->json([
            'status' => true,
            'message' => 'Categories fetched',
            'data' => BlogCategory::with('parent')->get()
        ]);
    }

    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'status' => 'nullable|boolean'
        ]);

        $category = BlogCategory::create([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'status' => $request->status ?? true
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Category created successfully',
            'data' => $category->load('parent')
        ], 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:blog_categories,id|different:id',
            'status' => 'nullable|boolean'
        ]);

        $category->update($request->only(['name', 'slug', 'status', 'parent_id']));
        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully',
            'data' => $category->load('parent')
        ]);
    }

    public function deleteCategory($id)
    {
        BlogCategory::findOrFail($id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    // === TAGS === //
    public function getTags()
    {
        return response()->json([
            'status' => true,
            'message' => 'Tags fetched',
            'data' => BlogTag::all()
        ]);
    }

    public function createTag(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $slug = $request->slug ?? Str::slug($request->name);
        
        $tag = BlogTag::firstOrCreate(
            ['slug' => $slug],
            ['name' => $request->name]
        );

        return response()->json([
            'status' => true,
            'message' => 'Tag processed successfully',
            'data' => $tag
        ], 201);
    }

    public function updateTag(Request $request, $id)
    {
        $tag = BlogTag::findOrFail($id);
        $tag->update($request->only(['name', 'slug']));
        return response()->json([
            'status' => true,
            'message' => 'Tag updated successfully',
            'data' => $tag
        ]);
    }

    public function deleteTag($id)
    {
        BlogTag::findOrFail($id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Tag deleted successfully'
        ]);
    }

    // === POSTS === //
    public function getPosts(Request $request)
    {
        $query = BlogPost::with(['categories', 'tags', 'author']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('blog_categories.id', $request->category_id);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'status' => true,
            'message' => 'Posts fetched',
            'data' => $query->latest()->paginate($request->get('limit', 15))
        ]);
    }

    public function show($id)
    {
        $post = BlogPost::with(['categories', 'tags', 'author', 'seoMetadata'])->findOrFail($id);
        return response()->json([
            'status' => true,
            'message' => 'Post details fetched',
            'data' => $post
        ]);
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:blog_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|string|in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
        }

        $post = BlogPost::create([
            'title' => $request->title,
            'slug' => $request->slug ?? Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'author_id' => auth()->id(),
            'status' => $request->status ?? 'published',
            'image_path' => $imagePath
        ]);

        if ($request->has('categories')) {
            $post->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        // Handle SEO
        if ($request->has('meta_title') || $request->has('meta_description')) {
            $post->saveSeoMetadata($request->meta_title, $request->meta_description);
        }

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => $post->load(['categories', 'tags', 'author', 'seoMetadata'])
        ], 201);
    }

    public function updatePost(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:blog_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|string|in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->except(['categories', 'tags', 'image', 'meta_title', 'meta_description']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = $request->file('image')->store('blogs', 'public');
        }

        $post->update($data);

        if ($request->has('categories')) {
            $post->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        // Handle SEO
        if ($request->has('meta_title') || $request->has('meta_description')) {
            $post->saveSeoMetadata($request->meta_title, $request->meta_description);
        }

        return response()->json([
            'status' => true,
            'message' => 'Post updated successfully',
            'data' => $post->load(['categories', 'tags', 'author', 'seoMetadata'])
        ]);
    }

    public function deletePost($id)
    {
        BlogPost::findOrFail($id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully'
        ]);
    }
}
