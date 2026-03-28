<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function filterAndSearch(array $filters)
    {
        $query = $this->model->with(['category', 'brand', 'variants', 'images']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        if (isset($filters['is_flash_sale'])) {
            $query->where('is_flash_sale', $filters['is_flash_sale']);
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

    public function findBySlug(string $slug)
    {
        return $this->model->with(['category', 'brand', 'variants', 'images'])->where('slug', $slug)->first();
    }
}
