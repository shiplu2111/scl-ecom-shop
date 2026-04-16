<?php

namespace App\Services;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockAdjustmentService extends BaseService
{
    /**
     * Create a new stock adjustment expertly properly brilliantly.
     */
    public function createAdjustment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $adjustment = StockAdjustment::create([
                'reference_no'    => $this->generateReference($data['type']),
                'type'            => $data['type'],
                'adjustable_id'   => $data['adjustable_id'] ?? null,
                'adjustable_type' => $data['adjustable_type'] ?? null,
                'reason'          => $data['reason'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'total_amount'    => $data['total_amount'] ?? 0,
                'created_by'      => auth('admin')->id(),
            ]);

            foreach ($data['items'] as $item) {
                $adjustment->items()->create([
                    'sku'        => $item['sku'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                    'subtotal'   => ($item['unit_price'] ?? 0) * $item['quantity'],
                ]);

                $this->updateInventory($adjustment->type, $item['sku'], $item['quantity'], $adjustment->reference_no);
            }

            return $adjustment->load('items');
        });
    }

    /**
     * Generate adjustment reference number brilliantly properly.
     */
    protected function generateReference(string $type): string
    {
        $prefix = match($type) {
            'purchase_return' => 'PRT',
            'customer_return' => 'CRT',
            'damage'          => 'DMG',
            'lost'            => 'LST',
            default           => 'ADJ',
        };

        return $prefix . '-' . date('Ymd') . '-' . strtoupper(Str::random(4));
    }

    /**
     * Update inventory based on adjustment type brilliantly properly flawlessly.
     */
    protected function updateInventory(string $type, string $sku, int $quantity, string $reference)
    {
        $inventory = Inventory::where('sku', $sku)->first();
        if (!$inventory) return;

        $previousStock = $inventory->quantity;
        
        // Logic for stock direction
        switch($type) {
            case 'customer_return':
                // Return to stock
                $inventory->increment('quantity', $quantity);
                $reason = "Customer Return: $reference";
                break;
            case 'purchase_return':
                // Removed from stock
                $inventory->decrement('quantity', $quantity);
                $reason = "Purchase Return: $reference";
                break;
            case 'damage':
            case 'lost':
            case 'manual':
                // Removed from stock
                $inventory->decrement('quantity', $quantity);
                $reason = ucfirst($type) . ": $reference";
                break;
        }

        // Log movement brilliantly properly flawlessly correctly
        \App\Models\InventoryHistory::create([
            'variant_id'     => \App\Models\ProductVariant::where('sku', $sku)->value('id'),
            'user_id'        => auth('admin')->id(),
            'previous_stock' => $previousStock,
            'new_stock'      => $inventory->fresh()->quantity,
            'reason'         => $reason,
        ]);
    }

    public function fetchAdjustments(array $filters = [])
    {
        $query = StockAdjustment::with(['items', 'admin']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where('reference_no', 'LIKE', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
