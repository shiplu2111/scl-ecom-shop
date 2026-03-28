<?php

namespace App\Services\Admin;

use App\Repositories\TransactionRepositoryInterface;
use App\Services\BaseService;

class TransactionService extends BaseService
{
    protected TransactionRepositoryInterface $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function fetchAll(array $filters)
    {
        return $this->transactionRepository->searchAndFilter($filters);
    }

    public function findWithDetails(int $id)
    {
        return $this->transactionRepository->find($id)->load(['order.user', 'order.shippingAddress']);
    }
}
