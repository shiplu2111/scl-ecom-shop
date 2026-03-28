<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group Admin
 * @subgroup Page Management
 */
class PageController extends Controller
{
    public function index()
    {
        return response()->json(Page::with('seoMetadata')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string'
        ]);

        $page = Page::create([
            'title' => $request->title,
            'slug' => $request->slug ?? Str::slug($request->title),
            'content' => $request->content,
            'status' => $request->status ?? 'published'
        ]);

        $page->saveSeoMetadata($request->meta_title, $request->meta_description);

        return response()->json($page->load('seoMetadata'), 201);
    }

    public function show($id)
    {
        return response()->json(Page::with('seoMetadata')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        
        $page->update($request->only(['title', 'slug', 'content', 'status']));

        if ($request->hasAny(['meta_title', 'meta_description'])) {
            $page->saveSeoMetadata($request->meta_title, $request->meta_description);
        }

        return response()->json($page->load('seoMetadata'));
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->seoMetadata()->delete();
        $page->delete();
        
        return response()->json(['message' => 'Page deleted']);
    }
}
