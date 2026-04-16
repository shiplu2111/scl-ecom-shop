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

    /**
     * Create a manual transaction (e.g., for COD).
     */
    public function createManual(array $data)
    {
        $transaction = $this->transactionRepository->create([
            'order_id'       => $data['order_id'],
            'amount'         => $data['amount'],
            'gateway'        => $data['gateway'] ?? 'cod',
            'transaction_id' => $data['transaction_id'] ?? ('MANUAL_' . time()),
            'status'         => 'success',
            'response_payload' => ['manual' => true, 'note' => $data['note'] ?? 'Manual transaction entry'],
        ]);

        // Automatically mark the order as paid if this is a full payment
        $order = $transaction->order;
        if ($order && $order->payment_status !== 'paid') {
            // Check if full amount is covered by transactions
            $totalPaid = $order->transactions()->where('status', 'success')->sum('amount');
            if ($totalPaid >= $order->grand_total) {
                $order->update([
                    'payment_status' => 'paid',
                    'order_status'   => 'processing' // Move from pending if it was COD
                ]);
            }
        }

        return $transaction;
    }
}
