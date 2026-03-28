<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * @group Admin
 * @subgroup Banner Management
 */
class BannerController extends BaseController
{
    /**
     * List all banners.
     */
    public function index()
    {
        $banners = Banner::orderBy('order')->get();
        return $this->successResponse($banners, 'Banners retrieved successfully');
    }

    /**
     * Store a new banner.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:main_banner,sub_banner',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:255',
            'status' => 'boolean',
            'order' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner = Banner::create($data);

        return $this->successResponse($banner, 'Banner created successfully', 201);
    }

    /**
     * Update an existing banner.
     */
    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:main_banner,sub_banner',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:255',
            'status' => 'boolean',
            'order' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return $this->successResponse($banner, 'Banner updated successfully');
    }

    /**
     * Delete a banner.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        
        $banner->delete();

        return $this->successResponse(null, 'Banner deleted successfully');
    }

    /**
     * Toggle banner status.
     */
    public function toggleStatus(Banner $banner)
    {
        $banner->update(['status' => !$banner->status]);
        return $this->successResponse($banner, 'Banner status toggled successfully');
    }
}
