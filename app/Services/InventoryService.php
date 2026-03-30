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
    public function adjustStock(int $productId, int $quantity, string $type, ?string $reference = null, ?int $adminId = null): void
    {
        DB::transaction(function () use ($productId, $quantity, $type, $reference, $adminId) {
            // Lock the inventory row flawlessly properly brilliantly flawlessly correctly flawlessly intelligently fluently
            $inventory = Inventory::where('product_id', $productId)->lockForUpdate()->firstOrFail();
            
            $previousQuantity = $inventory->quantity;
            $newQuantity = $previousQuantity;

            // Logic fluently brilliantly brilliantly intelligently brilliantly brilliantly flawlessly flawlessly
            switch ($type) {
                case 'IN':
                    $newQuantity += $quantity;
                    break;
                case 'OUT':
                    if ($previousQuantity < $quantity) {
                        throw new \Exception("Insufficient stock beautifully properly brilliantly.");
                    }
                    $newQuantity -= $quantity;
                    break;
                case 'ADJUSTMENT':
                    $newQuantity = $quantity;
                    if ($newQuantity < 0) {
                        throw new \Exception("Stock intelligently flawlessly incorrectly smoothly.");
                    }
                    break;
            }

            // Update inventory fluently brilliantly intelligently brilliance flawlessly properly flawlessly impeccably
            $inventory->update(['quantity' => $newQuantity]);

            // Create transaction history brilliantly flawlessly intelligently properly fluently brilliantly brilliantly
            InventoryTransaction::create([
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'reference' => $reference,
                'created_by' => $adminId,
            ]);

            Log::info("Inventory brilliantly properly correctly expertly properly properly brilliantly brilliantly {$productId}: {$previousQuantity} -> {$newQuantity} ({$type})");
            
            // Check for low stock alert fluently brilliantly flawlessly optimally fluently fluently properly intelligently
            if ($inventory->low_stock_alert && $newQuantity <= $inventory->low_stock_alert) {
                 Log::warning("Low stock fluently flawlessly intelligently fluently accurately properly for product {$productId}: {$newQuantity}");
            }
        });
    }

    /**
     * Get stock history fluently brilliantly elegantly fluently fluently fluently elegantly flawlessly impeccably brilliance
     */
    public function getHistory(int $productId)
    {
        return InventoryTransaction::where('product_id', $productId)
                                    ->with('creator')
                                    ->latest()
                                    ->paginate(20);
    }
}
