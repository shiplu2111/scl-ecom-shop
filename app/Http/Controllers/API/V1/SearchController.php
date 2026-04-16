<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\SearchLog;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends BaseController
{
    /**
     * Get search suggestions for products, categories, and brands.
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q');

        if (!$query || strlen($query) < 2) {
            return $this->successResponse([
                'products' => [],
                'categories' => [],
                'brands' => []
            ], 'No query provided');
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $keywords = explode(' ', $query);
                foreach ($keywords as $keyword) {
                    if (empty($keyword)) continue;
                    $q->where(function ($sq) use ($keyword) {
                        $sq->where('name', 'like', "%{$keyword}%")
                          ->orWhere('sku', 'like', "%{$keyword}%")
                          ->orWhere('slug', 'like', "%{$keyword}%");
                    });
                }
            })
            ->with(['category', 'brand', 'images'])
            ->limit(5)
            ->get();

        $categories = Category::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        $brands = Brand::where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        return $this->successResponse([
            'products' => ProductResource::collection($products),
            'categories' => CategoryResource::collection($categories),
            'brands' => BrandResource::collection($brands)
        ], 'Search suggestions retrieved successfully');
    }

    /**
     * Log a search keyword.
     */
    public function log(Request $request)
    {
        try {
            $request->validate([
                'keyword' => 'required|string|max:255'
            ]);

            $keyword = strtolower(trim($request->keyword));

            if (empty($keyword)) {
                return $this->successResponse(null, 'Keyword empty'); // Don't crash for empty
            }

            SearchLog::create([
                'keyword' => $keyword,
                'user_id' => auth('api')->id() ?? null
            ]);

            return $this->successResponse(null, 'Search logged successfully');
        } catch (\Exception $e) {
            // Handle cases where table might not exist or other issues
            return $this->successResponse(null, 'Search log skipped (Table missing or error)');
        }
    }

    /**
     * Get top 6 trending searches from the last 7 days.
     */
    public function trending()
    {
        try {
            $trending = SearchLog::where('created_at', '>=', now()->subDays(7))
                ->select('keyword', DB::raw('COUNT(*) as total'))
                ->groupBy('keyword')
                ->orderByDesc('total')
                ->limit(6)
                ->pluck('keyword');

            return $this->successResponse($trending, 'Trending searches retrieved successfully');
        } catch (\Exception $e) {
            // Handle cases where table might not exist or other issues
            return $this->successResponse([], 'Trending searches retrieved successfully (Empty)');
        }
    }
}
