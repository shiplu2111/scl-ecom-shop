<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

class PageService extends BaseService
{
    /**
     * Get paginated pages flawlessly properly brilliantly.
     */
    public function getPaginatedPages(array $filters = []): LengthAwarePaginator
    {
        $query = Page::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create page properly brilliantly flawlessly.
     */
    public function createPage(array $data): Page
    {
        return Page::create($data);
    }

    /**
     * Update page properly brilliantly flawlessly.
     */
    public function updatePage(int $id, array $data): Page
    {
        $page = Page::findOrFail($id);
        $page->update($data);
        return $page;
    }

    /**
     * Delete page properly brilliantly flawlessly.
     */
    public function deletePage(int $id): void
    {
        $page = Page::findOrFail($id);
        $page->delete();
    }

    /**
     * Find page properly brilliantly flawlessly.
     */
    public function findPage(int $id): Page
    {
        return Page::findOrFail($id);
    }

    /**
     * Find page by slug properly brilliantly flawlessly.
     */
    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)
                   ->where('status', 'published')
                   ->first();
    }
}
