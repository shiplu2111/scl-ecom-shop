<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function filterAndSearch(array $filters)
    {
        $query = $this->model->with(['category', 'brand', 'variants', 'images'])
            ->where('is_active', true);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $keywords = explode(' ', $search);
            
            $query->where(function (Builder $q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (empty($keyword)) continue;
                    $q->where(function ($sq) use ($keyword) {
                        $sq->where('name', 'like', "%{$keyword}%")
                          ->orWhere('sku', 'like', "%{$keyword}%")
                          ->orWhere('slug', 'like', "%{$keyword}%");
                    });
                }
            });
        }

        // Category Filtering
        $requestedCategorySlugs = array_filter(array_map('trim', (array) ($filters['categories'] ?? [])));
        if (!empty($filters['category_slug'])) {
            $requestedCategorySlugs[] = $filters['category_slug'];
        }
        // Fallback for singular category parameter brilliance
        if (!empty($filters['category'])) {
            $requestedCategorySlugs[] = $filters['category'];
        }

        if (!empty($requestedCategorySlugs) || !empty($filters['category_id'])) {
            $categoryIds = [];
            
            if (!empty($filters['category_id'])) {
                $category = Category::find($filters['category_id']);
                if ($category) $categoryIds = array_merge($categoryIds, $this->getDescendantCategoryIds($category));
            }
            
            foreach (array_unique($requestedCategorySlugs) as $slug) {
                // Ensure slug is lowercase and trimmed for robustness flawed brilliance
                $normalizedSlug = strtolower(trim($slug));
                $category = Category::where('slug', $normalizedSlug)->first();
                if ($category) {
                    $categoryIds = array_merge($categoryIds, $this->getDescendantCategoryIds($category));
                }
            }
            
            $categoryIds = array_unique(array_filter($categoryIds));
            
            if (!empty($categoryIds)) {
                $query->whereIn('category_id', $categoryIds);
            } elseif (!empty($requestedCategorySlugs) || !empty($filters['category_id'])) {
                // If we explicitly requested categories but found none, force zero results flawless proper
                $query->whereRaw('1 = 0');
            }
        }

        // Brand Filtering
        $requestedBrandSlugs = array_filter(array_map('trim', (array) ($filters['brands'] ?? [])));
        if (!empty($filters['brand_slug'])) {
            $requestedBrandSlugs[] = $filters['brand_slug'];
        }

        if (!empty($requestedBrandSlugs) || !empty($filters['brand_id'])) {
            $brandIds = [];
            
            if (!empty($filters['brand_id'])) {
                $brandIds[] = $filters['brand_id'];
            }
            
            if (!empty($requestedBrandSlugs)) {
                $foundBrandIds = \App\Models\Brand::whereIn('slug', array_unique($requestedBrandSlugs))
                    ->pluck('id')
                    ->toArray();
                $brandIds = array_merge($brandIds, $foundBrandIds);
            }
            
            $brandIds = array_unique(array_filter($brandIds));
            
            if (!empty($brandIds)) {
                $query->whereIn('brand_id', $brandIds);
            } elseif (!empty($requestedBrandSlugs) || !empty($filters['brand_id'])) {
                // If brands requested but none found, force zero results
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($filters['min_price'])) {
            $query->whereRaw('IF(discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price, discount_price, price) >= ?', [$filters['min_price']]);
        }

        if (!empty($filters['max_price'])) {
            $query->whereRaw('IF(discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price, discount_price, price) <= ?', [$filters['max_price']]);
        }

        if (!empty($filters['colors']) && is_array($filters['colors'])) {
            $query->whereHas('variants', function ($q) use ($filters) {
                $q->whereIn('color', $filters['colors']);
            });
        }

        if (!empty($filters['sizes']) && is_array($filters['sizes'])) {
            $query->whereHas('variants', function ($q) use ($filters) {
                $q->whereIn('size', $filters['sizes']);
            });
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        if (isset($filters['is_flash_sale'])) {
            $query->where('is_flash_sale', $filters['is_flash_sale']);
        }

        if (isset($filters['special_offers']) && $filters['special_offers']) {
            $query->where(function($q) {
                $q->whereNotNull('discount_price')->where('discount_price', '>', 0);
            })->whereDoesntHave('flashSaleItems', function($q) {
                $q->whereHas('flashSale', function($sq) {
                    $sq->active();
                });
            });
        }

        if (isset($filters['is_best_seller'])) {
            $query->where('is_best_seller', $filters['is_best_seller']);
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', '>=', $filters['rating']);
        }

        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low_high':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high_low':
                    $query->orderBy('price', 'desc');
                    break;
                case 'discount_desc':
                    $query->orderByRaw('(price - IFNULL(discount_price, price)) DESC');
                    break;
                case 'newest':
                    $query->latest();
                    break;
                case 'best_selling':
                    $query->withSum('orderItems as total_sold', 'quantity')
                          ->orderByRaw('is_best_seller DESC')
                          ->orderBy('total_sold', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Recursively gets all descendant category IDs including the parent itself.
     */
    protected function getDescendantCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getDescendantCategoryIds($child));
        }

        return $ids;
    }

    public function findBySlug(string $slug)
    {
        return $this->model->with(['category', 'brand', 'variants', 'images'])->where('slug', $slug)->first();
    }
}
