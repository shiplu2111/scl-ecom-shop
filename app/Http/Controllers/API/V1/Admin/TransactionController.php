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
}
