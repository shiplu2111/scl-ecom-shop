<?php

namespace App\Services;

use App\Repositories\BrandRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandService extends BaseService
{
    protected BrandRepositoryInterface $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function fetchAll()
    {
        return $this->brandRepository->all();
    }

    public function findBySlug(string $slug)
    {
        return $this->brandRepository->all()->where('slug', $slug)->first()?->load('seoMetadata');
    }

    public function createBrand(array $data)
    {
        $data['slug'] = $this->generateUniqueSlug($data['name'], \App\Models\Brand::class);
        
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['logo'] = $data['logo']->store('brands', 'public');
        }

        $brand = $this->brandRepository->create($data);

        if (array_key_exists('meta_title', $data) || array_key_exists('meta_description', $data)) {
            $brand->saveSeoMetadata($data['meta_title'] ?? null, $data['meta_description'] ?? null);
        }

        return $brand->load('seoMetadata');
    }

    public function updateBrand(int $id, array $data)
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], \App\Models\Brand::class, $id);
        } elseif (isset($data['slug'])) {
             $data['slug'] = $this->generateUniqueSlug($data['slug'], \App\Models\Brand::class, $id);
        }

        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $brand = $this->brandRepository->find($id);
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $data['logo']->store('brands', 'public');
        }

        $brand = $this->brandRepository->update($id, $data);

        if (array_key_exists('meta_title', $data) || array_key_exists('meta_description', $data)) {
            $brand->saveSeoMetadata($data['meta_title'] ?? null, $data['meta_description'] ?? null);
        }

        return $brand->load('seoMetadata');
    }

    public function deleteBrand(int $id)
    {
        $brand = $this->brandRepository->find($id);
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        return $this->brandRepository->delete($id);
    }
}
