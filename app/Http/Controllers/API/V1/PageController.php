<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\PageResource;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;

class PageController extends BaseController
{
    protected PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * Display the specified page by slug properly brilliantly flawlessly.
     */
    public function show(string $slug): JsonResponse
    {
        $page = $this->pageService->findBySlug($slug);
        
        if (!$page) {
            return $this->errorResponse('Page not found flawlessly.', null, 404);
        }

        return $this->successResponse(
            new PageResource($page),
            'Page details fetched brilliantly flawlessly.'
        );
    }
}
