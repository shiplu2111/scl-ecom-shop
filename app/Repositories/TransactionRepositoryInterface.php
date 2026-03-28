<?php

namespace App\Repositories;

interface TransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function searchAndFilter(array $filters);
}
