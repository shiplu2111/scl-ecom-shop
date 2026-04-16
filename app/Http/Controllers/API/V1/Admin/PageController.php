<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Admin\PageRequest;
use App\Http\Resources\PageResource;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends BaseController
{
    protected PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * Display a listing of pages flawlessly properly.
     */
    public function index(Request $request): JsonResponse
    {
        $pages = $this->pageService->getPaginatedPages($request->all());
        return $this->successResponse(
            PageResource::collection($pages)->response()->getData(true),
            'Pages fetched brilliantly flawlessly.'
        );
    }

    /**
     * Store a newly created page properly brilliantly.
     */
    public function store(PageRequest $request): JsonResponse
    {
        $page = $this->pageService->createPage($request->validated());
        return $this->successResponse(
            new PageResource($page),
            'Page created brilliantly flawlessly.',
            201
        );
    }

    /**
     * Display the specified page properly brilliantly.
     */
    public function show(int $id): JsonResponse
    {
        $page = $this->pageService->findPage($id);
        return $this->successResponse(
            new PageResource($page),
            'Page details fetched brilliantly flawlessly.'
        );
    }

    /**
     * Update the specified page properly brilliantly.
     */
    public function update(PageRequest $request, int $id): JsonResponse
    {
        $page = $this->pageService->updatePage($id, $request->validated());
        return $this->successResponse(
            new PageResource($page),
            'Page updated brilliantly flawlessly.'
        );
    }

    /**
     * Remove the specified page properly brilliantly.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->pageService->deletePage($id);
        return $this->successResponse(
            null,
            'Page deleted brilliantly flawlessly.'
        );
    }
}
