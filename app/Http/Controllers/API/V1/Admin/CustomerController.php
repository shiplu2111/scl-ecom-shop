<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Resources\CustomerResource;
use App\Services\Admin\CustomerService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Customer Management
 */
class CustomerController extends BaseController
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * List all customers with search and pagination.
     */
    public function index(Request $request)
    {
        $customers = $this->customerService->fetchAll($request->all());
        return $this->successResponse(CustomerResource::collection($customers), 'Customers fetched successfully');
    }

    /**
     * View detailed customer info.
     */
    public function show(int $id)
    {
        $customer = $this->customerService->findWithDetails($id);
        if (!$customer) return $this->errorResponse('Customer not found', 404);

        return $this->successResponse(new CustomerResource($customer), 'Customer details fetched successfully');
    }

    /**
     * Toggle customer active status.
     */
    public function toggleStatus(int $id)
    {
        $customer = $this->customerService->toggleStatus($id);
        return $this->successResponse(new CustomerResource($customer), 'Customer status toggled successfully');
    }

    /**
     * Get customer activity logs.
     */
    public function activities(int $id)
    {
        $activities = $this->customerService->getCustomerActivities($id);
        return $this->successResponse($activities, 'Customer activities fetched successfully');
    }

    public function export(Request $request)
    {
        $ids = $request->has('ids') ? explode(',', $request->ids) : [];
        $callback = $this->customerService->exportCustomers($ids);
        
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
