<?php

namespace App\Services\Admin;

use App\Repositories\UserRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class CustomerService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function fetchAll(array $filters)
    {
        return $this->userRepository->searchAndFilter($filters);
    }

    public function findWithDetails(int $id)
    {
        return $this->userRepository->find($id)->load([
            'addresses', 
            'addresses.division', 
            'addresses.district', 
            'addresses.thana', 
            'orders' => function($query) {
                $query->withCount('items')->latest();
            }
        ]);
    }

    public function toggleStatus(int $id)
    {
        return $this->userRepository->toggleStatus($id);
    }

    public function getCustomerActivities(int $id)
    {
        return \Spatie\Activitylog\Models\Activity::where(function ($query) use ($id) {
                $query->where('causer_id', $id)
                      ->where('causer_type', \App\Models\User::class);
            })
            ->orWhere(function ($query) use ($id) {
                $query->where('subject_id', $id)
                      ->where('subject_type', \App\Models\User::class);
            })
            ->latest()
            ->paginate(20);
    }

    public function deleteCustomer(int $id)
    {
        return $this->userRepository->delete($id);
    }

    public function exportCustomers(array $ids = [])
    {
        $query = \App\Models\User::withCount('orders')->with(['orders']);
        
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $customers = $query->get();

        $headers = [
            'ID', 'Name', 'Email', 'Phone', 'Joined Date', 'Total Orders', 'Total Spent (৳)'
        ];

        return function() use ($customers, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    (string) $customer->id,
                    (string) $customer->name,
                    (string) $customer->email,
                    (string) ($customer->phone ?? 'N/A'),
                    (string) $customer->created_at->format('Y-m-d'),
                    (string) $customer->orders_count,
                    (string) $customer->orders->sum('grand_total')
                ]);
            }

            fclose($file);
        };
    }
}
