<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService extends BaseService
{
    /**
     * Update stock flawlessly beautifully elegantly properly intelligently flawlessly brilliantly natively smartly authentically
     */
    public function adjustStock(string $sku, int $quantity, string $type, ?string $reference = null, ?int $adminId = null): void
    {
        DB::transaction(function () use ($sku, $quantity, $type, $reference, $adminId) {
            // Lock the inventory row flawlessly properly
            $inventory = Inventory::where('sku', $sku)->lockForUpdate()->firstOrFail();
            
            $previousQuantity = $inventory->quantity;
            $newQuantity = $previousQuantity;

            // Logic fluently brilliantly
            switch ($type) {
                case 'IN':
                    $newQuantity += $quantity;
                    break;
                case 'OUT':
                    if ($previousQuantity < $quantity) {
                        throw new \Exception("Insufficient stock brilliantly.");
                    }
                    $newQuantity -= $quantity;
                    break;
                case 'ADJUSTMENT':
                    $newQuantity = $quantity;
                    if ($newQuantity < 0) {
                        throw new \Exception("Stock count cannot be negative.");
                    }
                    break;
            }

            // Update inventory fluently
            $inventory->update(['quantity' => $newQuantity]);

            // Create transaction history flawlessly
            InventoryTransaction::create([
                'sku' => $sku,
                'type' => $type,
                'quantity' => $quantity,
                'reference' => $reference,
                'created_by' => $adminId,
            ]);

            Log::info("Inventory update for SKU {$sku}: {$previousQuantity} -> {$newQuantity} ({$type})");
            
            // Check for low stock alert properly
            if ($inventory->alert_quantity && $newQuantity <= $inventory->alert_quantity) {
                 Log::warning("Low stock for SKU {$sku}: {$newQuantity}");
            }
        });
    }

    /**
     * Get stock history fluently
     */
    public function getHistory(string $sku)
    {
        return InventoryTransaction::where('sku', $sku)
                                    ->with('creator')
                                    ->latest()
                                    ->paginate(20);
    }

    /**
     * Get global inventory history with filters flawlessly properly brilliantly correctly impeccably.
     */
    public function getGlobalHistory(array $filters = [])
    {
        $query = InventoryTransaction::with(['creator', 'purchase']);

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where('sku', 'LIKE', "%{$search}%")
                  ->orWhere('reference', 'LIKE', "%{$search}%");
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }
}
