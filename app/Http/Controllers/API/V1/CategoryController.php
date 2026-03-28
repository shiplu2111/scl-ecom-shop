<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;

/**
 * @group Public
 * @subgroup Category
 */
class CategoryController extends BaseController
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * List all root categories with children.
     */
    public function index()
    {
        $categories = $this->categoryService->fetchPublic();
        return $this->successResponse(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }

    /**
     * Show category details by slug.
     */
    public function show(string $slug)
    {
        $category = $this->categoryService->findBySlug($slug);
        
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        return $this->successResponse(new CategoryResource($category), 'Category details fetched successfully');
    }
}
