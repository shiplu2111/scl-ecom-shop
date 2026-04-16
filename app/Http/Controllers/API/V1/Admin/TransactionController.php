<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Resources\TransactionResource;
use App\Services\Admin\TransactionService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Transaction Management
 */
class TransactionController extends BaseController
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * List transactions with filters and search.
     */
    public function index(Request $request)
    {
        $transactions = $this->transactionService->fetchAll($request->all());
        return $this->successResponse(TransactionResource::collection($transactions), 'Transactions fetched successfully');
    }

    /**
     * View full transaction details.
     */
    public function show(int $id)
    {
        $transaction = $this->transactionService->findWithDetails($id);
        if (!$transaction) return $this->errorResponse('Transaction not found', 404);

        return $this->successResponse(new TransactionResource($transaction), 'Transaction details fetched successfully');
    }

    /**
     * Store a manual transaction (e.g., for COD).
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'amount'         => 'required|numeric|min:0',
            'gateway'        => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:255',
        ]);

        try {
            $transaction = $this->transactionService->createManual($request->all());
            return $this->successResponse(new TransactionResource($transaction), 'Manual transaction recorded successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to record transaction: ' . $e->getMessage(), 500);
        }
    }
}
