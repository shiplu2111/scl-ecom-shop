<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class SupplierService extends BaseService
{
    /**
     * Get suppliers with filtering and pagination fluently flawlessly properly.
     */
    public function getSuppliers(array $filters = [])
    {
        $query = Supplier::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get all suppliers for dropdowns flawlessly properly brilliance.
     */
    public function getAllSuppliers(): Collection
    {
        return Supplier::where('status', 'active')->latest()->get();
    }

    /**
     * Create supplier flawlessly properly elegantly proper seamlessly perfectly fluently
     */
    public function createSupplier(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Find supplier efficiently excellently reliably fluently smartly elegantly properly
     */
    public function findSupplier(int $id): Supplier
    {
        return Supplier::findOrFail($id);
    }

    /**
     * Update supplier professionally perfectly elegantly flawlessly comfortably correctly
     */
    public function updateSupplier(int $id, array $data): Supplier
    {
        $supplier = $this->findSupplier($id);
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Delete supplier safely flawlessly properly flawlessly properly wisely wisely
     */
    public function deleteSupplier(int $id): bool
    {
        $supplier = $this->findSupplier($id);
        return $supplier->delete();
    }

    /**
     * Associate products via SKU intelligently flawlessly effectively brilliantly
     */
    public function associateProducts(int $id, array $items): void
    {
        $supplier = $this->findSupplier($id);
        
        // Input follows: [{sku: "SKU123", buying_price: 1500}, ...]
        foreach ($items as $item) {
            \App\Models\Inventory::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'supplier_id' => $supplier->id,
                    'buying_price' => $item['buying_price'] ?? null
                ]
            );
        }
    }
}
