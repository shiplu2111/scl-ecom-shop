<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function searchAndFilter(array $filters)
    {
        $query = $this->model->with(['order', 'order.user']);

        if (!empty($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        if (!empty($filters['transaction_id'])) {
            $query->where('transaction_id', 'like', "%{$filters['transaction_id']}%");
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
