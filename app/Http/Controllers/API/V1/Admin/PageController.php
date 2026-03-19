<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group Admin
 */
class PageController extends Controller
{
    public function index()
    {
        return response()->json(Page::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        $page = Page::create([
            'title' => $request->title,
            'slug' => $request->slug ?? Str::slug($request->title),
            'content' => $request->content,
            'meta_description' => $request->meta_description,
            'status' => $request->status ?? 'published'
        ]);

        return response()->json($page, 201);
    }

    public function show($id)
    {
        return response()->json(Page::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $page->update($request->all());

        return response()->json($page);
    }

    public function destroy($id)
    {
        Page::findOrFail($id)->delete();
        return response()->json(['message' => 'Page deleted']);
    }
}
