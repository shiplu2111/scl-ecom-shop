<?php

namespace App\Repositories;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function filterAndSearch(array $filters);
    public function findBySlug(string $slug);
}
