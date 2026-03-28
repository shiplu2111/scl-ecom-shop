<?php

namespace App\Services;

use App\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryService extends BaseService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function fetchAll()
    {
        return $this->categoryRepository->all();
    }

    public function fetchTree()
    {
        return $this->categoryRepository->all()->where('parent_id', null)->load('children');
    }

    public function fetchPublic()
    {
        return $this->fetchTree();
    }

    public function find(int $id)
    {
        return $this->categoryRepository->find($id);
    }

    public function findBySlug(string $slug)
    {
        return $this->categoryRepository->all()->where('slug', $slug)->first()?->load('seoMetadata');
    }

    public function createCategory(array $data)
    {
        $data['slug'] = $this->generateUniqueSlug($data['name'], \App\Models\Category::class);
        
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image'] = $data['image']->store('categories', 'public');
        }

        $category = $this->categoryRepository->create($data);

        if (array_key_exists('meta_title', $data) || array_key_exists('meta_description', $data)) {
            $category->saveSeoMetadata($data['meta_title'] ?? null, $data['meta_description'] ?? null);
        }

        return $category->load('seoMetadata');
    }

    public function updateCategory(int $id, array $data)
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], \App\Models\Category::class, $id);
        } elseif (isset($data['slug'])) {
             $data['slug'] = $this->generateUniqueSlug($data['slug'], \App\Models\Category::class, $id);
        }

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $category = $this->categoryRepository->find($id);
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $data['image']->store('categories', 'public');
        }

        $category = $this->categoryRepository->update($id, $data);

        if (array_key_exists('meta_title', $data) || array_key_exists('meta_description', $data)) {
            $category->saveSeoMetadata($data['meta_title'] ?? null, $data['meta_description'] ?? null);
        }

        return $category->load('seoMetadata');
    }

    public function deleteCategory(int $id)
    {
        $category = $this->categoryRepository->find($id);
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        return $this->categoryRepository->delete($id);
    }
}
