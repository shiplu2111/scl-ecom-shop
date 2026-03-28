<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * @group Admin
 * @subgroup System Tools
 */
class AnnouncementController extends Controller
{
    /**
     * Display a listing of the announcements.
     */
    public function index()
    {
        $announcements = Announcement::latest()->get();
        return response()->json([
            'status' => true,
            'message' => 'Announcements retrieved successfully',
            'data' => $announcements
        ]);
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'is_active' => 'boolean',
            'min_order_amount' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('announcements', 'public');
            $data['image_url'] = $path;
        }

        $announcement = Announcement::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Announcement created successfully',
            'data' => $announcement
        ]);
    }

    /**
     * Display the specified announcement.
     */
    public function show(Announcement $announcement)
    {
        return response()->json([
            'status' => true,
            'message' => 'Announcement retrieved successfully',
            'data' => $announcement
        ]);
    }

    /**
     * Update the specified announcement in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'type' => 'string',
            'is_active' => 'boolean',
            'min_order_amount' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        if ($request->hasFile('image')) {
            // Delete old image if it exists and is not a full URL
            if ($announcement->getRawOriginal('image_url') && !filter_var($announcement->getRawOriginal('image_url'), FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($announcement->getRawOriginal('image_url'));
            }
            $path = $request->file('image')->store('announcements', 'public');
            $data['image_url'] = $path;
        }

        $announcement->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Announcement updated successfully',
            'data' => $announcement
        ]);
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(Announcement $announcement)
    {
        // Delete image if it exists and is not a full URL
        if ($announcement->getRawOriginal('image_url') && !filter_var($announcement->getRawOriginal('image_url'), FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($announcement->getRawOriginal('image_url'));
        }
        $announcement->delete();

        return response()->json([
            'status' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Announcement $announcement)
    {
        $announcement->update([
            'is_active' => !$announcement->is_active
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Announcement status toggled successfully',
            'data' => $announcement
        ]);
    }
}
