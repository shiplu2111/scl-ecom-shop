<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Get active banners for public storefront.
     */
    public function index()
    {
        $banners = Banner::where('status', true)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'main' => $banners->where('type', 'main_banner')->values(),
            'sub' => $banners->where('type', 'sub_banner')->take(4)->values(),
        ]);
    }
}
