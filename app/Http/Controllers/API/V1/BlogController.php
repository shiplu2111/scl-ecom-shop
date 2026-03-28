<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @group Public
 * @subgroup Blog
 */
class BlogController extends Controller
{
    public function getCategories()
    {
        $categories = Cache::rememberForever('blog.categories.active', function () {
            return BlogCategory::where('status', true)->get();
        });
        return response()->json($categories);
    }

    public function getTags()
    {
        $tags = Cache::rememberForever('blog.tags.all', function () {
            return BlogTag::all();
        });
        return response()->json($tags);
    }

    public function getPosts(Request $request)
    {
        $cacheKey = 'blog.posts.' . md5(json_encode($request->all()));
        
        $posts = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request) {
            $query = BlogPost::with(['category', 'tags', 'author'])->where('status', 'published');

            if ($request->has('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            if ($request->has('tag')) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('slug', $request->tag);
                });
            }

            return $query->paginate(15);
        });

        return response()->json($posts);
    }

    public function showPost($slug)
    {
        $post = Cache::rememberForever('blog.post.' . $slug, function () use ($slug) {
            return BlogPost::with(['category', 'tags', 'author'])
                ->where('status', 'published')
                ->where('slug', $slug)
                ->firstOrFail();
        });

        return response()->json($post);
    }
}
