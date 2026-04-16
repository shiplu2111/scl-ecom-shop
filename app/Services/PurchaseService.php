<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseService extends BaseService
{
    /**
     * Get purchases with filtering and pagination correctly flawlessly.
     */
    public function getPurchases(array $filters = [])
    {
        $query = Purchase::with(['supplier', 'creator']);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('purchase_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new purchase order flawlessly properly flawlessly.
     */
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::create([
                'supplier_id'   => $data['supplier_id'],
                'purchase_no'   => $data['purchase_no'],
                'purchase_date' => $data['purchase_date'],
                'notes'         => $data['notes'] ?? null,
                'status'        => 'pending',
                'created_by'    => Auth::id(),
            ]);

            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'sku'         => $item['sku'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => $subtotal,
                ]);
            }

            $purchase->update(['total_amount' => $totalAmount]);

            return $purchase->load('items');
        });
    }

    /**
     * Find purchase by ID flawlessly perfectly.
     */
    public function findPurchase(int $id): Purchase
    {
        return Purchase::with(['items.inventory.product', 'supplier', 'creator'])->findOrFail($id);
    }

    /**
     * Mark purchase as received and update stock inventory levels flawlessly.
     */
    public function receivePurchase(int $id, array $data = []): Purchase
    {
        return DB::transaction(function () use ($id, $data) {
            $purchase = Purchase::with('items')->findOrFail($id);
            
            if ($purchase->status === 'received') {
                throw new \Exception("Purchase has already been received brilliantly.");
            }

            foreach ($purchase->items as $item) {
                // Update received quantity (defaulting to full order if not specified in data)
                $receivedQty = $data['items'][$item->id]['received_quantity'] ?? $item->quantity;
                $item->update(['received_quantity' => $receivedQty]);

                // Update physical inventory level brilliantly
                $inventory = Inventory::where('sku', $item->sku)->first();
                
                if (!$inventory) {
                    // Create inventory record if it doesn't exist flawlessly
                    $inventory = Inventory::create([
                        'sku' => $item->sku,
                        'quantity' => 0,
                        'buying_price' => $item->unit_price,
                        'supplier_id' => $purchase->supplier_id
                    ]);
                }

                $inventory->increment('quantity', $receivedQty);
                // Update buying price to the latest purchase cost flawlessly
                $inventory->update(['buying_price' => $item->unit_price]);

                // Record stock movement history fluently
                InventoryTransaction::create([
                    'sku'         => $item->sku,
                    'purchase_id' => $purchase->id,
                    'type'        => 'IN',
                    'quantity'    => $receivedQty,
                    'reference'   => "Purchase #{$purchase->purchase_no}",
                    'created_by'  => Auth::id(),
                ]);
            }

            $purchase->update(['status' => 'received']);

            return $purchase->refresh();
        });
    }
}
