<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;

/**
 * @group Admin
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
     * Full list for admin management.
     */
    public function index()
    {
        $categories = $this->categoryService->fetchTree();
        return $this->successResponse(CategoryResource::collection($categories), 'All categories retrieved successfully');
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());
        return $this->successResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function show(int $id)
    {
        $category = $this->categoryService->find($id)?->load('seoMetadata');
        
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        return $this->successResponse(new CategoryResource($category), 'Category details fetched successfully');
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = $this->categoryService->updateCategory($id, $request->validated());
        return $this->successResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(int $id)
    {
        $this->categoryService->deleteCategory($id);
        return $this->successResponse(null, 'Category deleted successfully');
    }
}
