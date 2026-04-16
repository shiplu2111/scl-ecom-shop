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
     * List categories with optional tree structure.
     */
    public function index(Request $request)
    {
        $rootOnly = $request->boolean('root_only', false);
        
        if ($rootOnly) {
            $categories = $this->categoryService->fetchRootCategories();
        } else {
            $categories = $this->categoryService->fetchPublic();
        }

        return $this->successResponse(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }

    /**
     * List subcategories for a given parent category.
     */
    public function subcategories(int $parentId)
    {
        $subcategories = $this->categoryService->fetchSubCategories($parentId);
        return $this->successResponse(CategoryResource::collection($subcategories), 'Subcategories retrieved successfully');
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
