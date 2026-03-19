<?php

namespace App\Services;

use App\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ProductService extends BaseService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function fetchFiltered(array $filters)
    {
        $cacheKey = 'products.filtered.' . md5(json_encode($filters));
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($filters) {
            return $this->productRepository->filterAndSearch($filters);
        });
    }

    public function findBySlug(string $slug)
    {
        return $this->productRepository->findBySlug($slug);
    }

    public function find(int $id)
    {
        return $this->productRepository->find($id);
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = Str::slug($data['name']);
            $product = $this->productRepository->create([
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'] ?? null,
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'sku'         => $data['sku'],
                'description' => $data['description'] ?? null,
                'price'       => $data['price'],
                'is_active'   => $data['is_active'] ?? true,
            ]);

            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variant) {
                    $product->variants()->create($variant);
                }
            }

            return $product->load(['variants', 'category', 'brand', 'images']);
        });
    }

    public function updateProduct(int $id, array $data)
    {
        // Simplistic wrapper assuming raw property array
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $this->productRepository->update($id, $data);
    }

    public function uploadImage(int $productId, \Illuminate\Http\UploadedFile $file, bool $isThumbnail = false)
    {
        $product = $this->productRepository->find($productId);
        
        $path = $file->store('products', 'public');
        
        return $product->images()->create([
            'image_path'   => $path,
            'is_thumbnail' => $isThumbnail,
        ]);
    }
}
