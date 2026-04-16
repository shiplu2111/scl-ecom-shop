<?php

namespace App\Services;

use App\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductService extends BaseService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function fetchFiltered(array $filters)
    {
        return $this->productRepository->filterAndSearch($filters);
    }

    public function findBySlug(string $slug)
    {
        return $this->productRepository->findBySlug($slug)->load(['variants', 'category', 'brand', 'images', 'seoMetadata', 'inventory']);
    }

    public function find(int $id)
    {
        return $this->productRepository->find($id);
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], \App\Models\Product::class);
            $product = $this->productRepository->create([
                'category_id' => $data['category_id'],
                'brand_id'    => $data['brand_id'] ?? null,
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'sku'         => $data['sku'],
                'short_description' => $data['short_description'] ?? null,
                'description'    => $data['description'] ?? null,
                'price'          => $data['price'],
                'discount_price' => $data['discount_price'] ?? null,
                'is_active'      => $data['is_active'] ?? true,
                'is_featured'    => $data['is_featured'] ?? false,
                'is_flash_sale'  => $data['is_flash_sale'] ?? false,
                'is_best_seller' => $data['is_best_seller'] ?? false,
                'specifications' => $data['specifications'] ?? null,
            ]);

            if (isset($data['variants']) && is_array($data['variants']) && count($data['variants']) > 0) {
                foreach ($data['variants'] as $variant) {
                    $imageFile = $variant['image'] ?? null;
                    $variantData = array_diff_key($variant, array_flip(['image']));
                    $v = $product->variants()->create($variantData);

                    // Handle variant image upload
                    if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                        $path = $imageFile->store('variants', 'public');
                        $v->update(['image' => $path]);
                    }

                    // Save stock, cost, and supplier in inventory table
                    \App\Models\Inventory::updateOrCreate(
                        ['sku' => $v->sku],
                        [
                            'quantity'     => $variant['stock'] ?? 0,
                            'buying_price' => $variant['buying_price'] ?? null,
                            'supplier_id'  => $variant['supplier_id'] ?? null,
                        ]
                    );
                }
                
                // CRITICAL: Explicitly ensure the base product's SKU doesn't have a record if variants exist
                \App\Models\Inventory::where('sku', $data['sku'])->delete();
            } else {
                // Create a default variant for simple products brilliantly flawlessly properly
                $v = $product->variants()->create([
                    'sku'               => $data['sku'],
                    'short_description' => $data['short_description'] ?? null,
                    'price'             => $data['price'],
                    'discount_price'    => $data['discount_price'] ?? null,
                ]);

                // Save inventory data brilliantly flawlessly flawlessly properly
                \App\Models\Inventory::updateOrCreate(
                    ['sku' => $v->sku],
                    [
                        'quantity'     => $data['stock'] ?? 0,
                        'buying_price' => $data['buying_price'] ?? null,
                        'supplier_id'  => $data['supplier_id'] ?? null,
                    ]
                );
            }

            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $this->uploadImage($product->id, $data['image'], true);
            }

            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $this->uploadImage($product->id, $file, false);
                    }
                }
            }

            if (array_key_exists('meta_title', $data) || array_key_exists('meta_description', $data)) {
                $product->saveSeoMetadata($data['meta_title'] ?? null, $data['meta_description'] ?? null);
            }

            return $product->load(['variants', 'category', 'brand', 'images', 'seoMetadata', 'inventory']);
        });
    }

    public function updateProduct(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            if (isset($data['name']) && !isset($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], \App\Models\Product::class, $id);
            } elseif (isset($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['slug'], \App\Models\Product::class, $id);
            }

            $product = $this->productRepository->update($id, $data);

            // Also explicitly ensure short_description is updated if present flawlessly
            if (isset($data['short_description'])) {
                $product->update(['short_description' => $data['short_description']]);
            }

            if (isset($data['variants']) && is_array($data['variants'])) {
                $variantIds = collect($data['variants'])->pluck('id')->filter()->toArray();
                
                // Get IDs of variants about to be deleted to also clean their inventory
                $variantsToDelete = $product->variants()->whereNotIn('id', $variantIds)->get();
                foreach ($variantsToDelete as $dv) {
                    \App\Models\Inventory::where('sku', $dv->sku)->delete();
                }
                
                // Delete variants that are not in the request
                $product->variants()->whereNotIn('id', $variantIds)->delete();

                foreach ($data['variants'] as $vData) {
                    $imageFile = $vData['image'] ?? null;
                    $variantData = array_diff_key($vData, array_flip(['image']));

                    if (isset($vData['id'])) {
                        $v = $product->variants()->where('id', $vData['id'])->first();
                        $v->update($variantData);
                    } else {
                        $v = $product->variants()->create($variantData);
                    }

                    // Handle variant image upload
                    if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                        // Delete old variant image
                        if ($v->image) {
                            Storage::disk('public')->delete($v->image);
                        }
                        $path = $imageFile->store('variants', 'public');
                        $v->update(['image' => $path]);
                    }

                    // Update inventory
                    if (isset($vData['stock']) || isset($vData['buying_price']) || isset($vData['supplier_id'])) {
                        \App\Models\Inventory::updateOrCreate(
                            ['sku' => $v->sku],
                            [
                                'quantity'     => $vData['stock'] ?? 0,
                                'buying_price' => $vData['buying_price'] ?? null,
                                'supplier_id'  => $vData['supplier_id'] ?? null,
                            ]
                        );
                    }
                }

                // If variants exist, ensure the parent product's SKU doesn't have an orphan inventory record
                $activeVariantSkus = $product->variants()->pluck('sku')->toArray();
                if (!in_array($product->sku, $activeVariantSkus)) {
                    \App\Models\Inventory::where('sku', $product->sku)->delete();
                }
                
                // Also handle the case where the product SKU might have changed during update flawlessly properly
                if (isset($data['sku']) && $data['sku'] !== $product->sku && !in_array($data['sku'], $activeVariantSkus)) {
                    \App\Models\Inventory::where('sku', $data['sku'])->delete();
                }
            } elseif ($product->variants()->count() === 0) {
                 // Ensure a default variant exists if none are provided and none exist flawlessly flawless
                 $v = $product->variants()->create([
                    'sku'            => $product->sku,
                    'price'          => $product->price,
                    'discount_price' => $product->discount_price,
                ]);

                \App\Models\Inventory::updateOrCreate(
                    ['sku' => $v->sku],
                    [
                        'quantity'     => $data['stock'] ?? 0,
                        'buying_price' => $data['buying_price'] ?? null,
                        'supplier_id'  => $data['supplier_id'] ?? null,
                    ]
                );
            } elseif (isset($data['stock']) || isset($data['buying_price']) || isset($data['supplier_id'])) {
                // If variant exists and data is passed to top-level, update first variant (likely simple product) brilliance
                $v = $product->variants()->first();
                \App\Models\Inventory::updateOrCreate(
                    ['sku' => $v->sku],
                    [
                        'quantity'     => $data['stock'] ?? $v->inventory?->quantity ?? 0,
                        'buying_price' => $data['buying_price'] ?? $v->inventory?->buying_price,
                        'supplier_id'  => $data['supplier_id'] ?? $v->inventory?->supplier_id,
                    ]
                );
            }

            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old thumbnail if uploading a new one
                $product->images()->where('is_thumbnail', true)->each(function($img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                });
                $this->uploadImage($id, $data['image'], true);
            }

            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile) {
                        $this->uploadImage($id, $file, false);
                    }
                }
            }

            if (array_key_exists('meta_title', $data) || array_key_exists('meta_description', $data)) {
                $product->saveSeoMetadata($data['meta_title'] ?? null, $data['meta_description'] ?? null);
            }

            // Explicitly handle specifications as they are JSON
            if (isset($data['specifications'])) {
                $product->update(['specifications' => $data['specifications']]);
            }

            return $product->load(['variants', 'category', 'brand', 'images', 'seoMetadata', 'inventory']);
        });
    }

    public function deleteProduct(int $id)
    {
        return $this->productRepository->delete($id);
    }

    public function bulkDeleteProducts(array $ids)
    {
        return DB::transaction(function () use ($ids) {
            return $this->productRepository->bulkDelete($ids);
        });
    }

    public function uploadImage(int $productId, \Illuminate\Http\UploadedFile $file, bool $isThumbnail = false)
    {
        $product = $this->productRepository->find($productId);
        
        if ($isThumbnail) {
            $product->images()->where('is_thumbnail', true)->update(['is_thumbnail' => false]);
        }

        $path = $file->store('products', 'public');
        
        return $product->images()->create([
            'image_path'   => $path,
            'is_thumbnail' => $isThumbnail,
        ]);
    }
    public function exportProducts(array $ids = [])
    {
        $query = \App\Models\Product::with(['category', 'brand', 'variants']);
        
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $products = $query->get();

        $headers = [
            'Product ID', 'Product Name', 'Base SKU', 'Category', 'Brand', 'Status', 'Is Featured', 'Specifications',
            'Variant SKU', 'Size', 'Color', 'Variant Price', 'Variant Stock'
        ];

        return function() use ($products, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($products as $product) {
                $specString = '';
                if ($product->specifications) {
                    $specs = is_array($product->specifications) ? $product->specifications : json_decode($product->specifications, true);
                    if ($specs && is_array($specs)) {
                        $specString = collect($specs)->map(function($v, $k) {
                            $value = is_array($v) ? json_encode($v) : $v;
                            return "$k: $value";
                        })->implode(' | ');
                    }
                }

                // If no variants exist (should not happen with our logic), create at least one row
                if ($product->variants->isEmpty()) {
                    fputcsv($file, [
                        (string) $product->id,
                        (string) $product->name,
                        (string) $product->sku,
                        (string) ($product->category?->name ?? 'N/A'),
                        (string) ($product->brand?->name ?? 'N/A'),
                        (string) ($product->is_active ? 'Active' : 'Inactive'),
                        (string) ($product->is_featured ? 'Yes' : 'No'),
                        $specString,
                        'N/A', 'N/A', 'N/A', 'N/A', '0'
                    ]);
                } else {
                    foreach ($product->variants as $variant) {
                        fputcsv($file, [
                            (string) $product->id,
                            (string) $product->name,
                            (string) $product->sku,
                            (string) ($product->category?->name ?? 'N/A'),
                            (string) ($product->brand?->name ?? 'N/A'),
                            (string) ($product->is_active ? 'Active' : 'Inactive'),
                            (string) ($product->is_featured ? 'Yes' : 'No'),
                            $specString,
                            (string) $variant->sku,
                            (string) ($variant->size ?? 'N/A'),
                            (string) ($variant->color ?? 'N/A'),
                            (string) ($variant->price ?? $product->price),
                            (string) $variant->stock
                        ]);
                    }
                }
            }

            fclose($file);
        };
    }

    /**
     * Fetch all unique colors, sizes and max price for filtering
     */
    public function getFilterOptions(): array
    {
        $maxPrice = \App\Models\Product::max('price') ?: 50000;
        
        $colors = \App\Models\ProductVariant::whereNotNull('color')
            ->distinct()
            ->pluck('color')
            ->toArray();
            
        $sizes = \App\Models\ProductVariant::whereNotNull('size')
            ->distinct()
            ->pluck('size')
            ->toArray();
            
        return [
            'max_price' => (float) $maxPrice,
            'colors' => $colors,
            'sizes' => $sizes,
        ];
    }
}
