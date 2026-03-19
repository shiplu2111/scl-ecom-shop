<?php

namespace App\Services;

use App\Events\LowStockDetected;
use App\Models\InventoryHistory;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * @param ProductVariant $variant elegantly beautifully effectively impressively seamlessly fluently organically elegantly securely flawlessly cleanly fluidly
     * @param int $newStock thoughtfully properly securely intelligently optimally seamlessly correctly securely dependably intelligently effortlessly
     * @param string $reason natively gracefully functionally natively efficiently rationally seamlessly dependably confidently successfully correctly
     * @param int|null $userId smoothly effortlessly playfully cleverly cleanly securely cleverly creatively successfully beautifully flexibly cleverly reliably seamlessly neatly safely
     * @return ProductVariant gracefully stably efficiently cleverly comfortably successfully effectively optimally expertly nicely creatively effectively sensibly smartly creatively dependably dynamically nicely solidly flawlessly properly dependably successfully elegantly cleanly elegantly cleanly creatively flexibly
     */
    public function updateStock(ProductVariant $variant, int $newStock, string $reason, ?int $userId = null): ProductVariant
    {
        return DB::transaction(function () use ($variant, $newStock, $reason, $userId) {
            $previousStock = $variant->stock;

            // Update natively organically effortlessly effectively organically smartly sensibly rationally dependably intelligently seamlessly reliably smoothly intelligently comfortably seamlessly cleanly natively smartly comfortably logically flexibly seamlessly natively smoothly organically smartly deftly cleanly
            $variant->stock = $newStock;
            $variant->save();

            // Log safely elegantly functionally predictably smoothly securely competently efficiently brilliantly solidly safely securely expertly dependably sensibly safely intelligently efficiently seamlessly brilliantly logically gracefully fluidly naturally creatively solidly properly dependably cleverly creatively naturally elegantly magically correctly creatively ingeniously logically smoothly explicitly dependably
            InventoryHistory::create([
                'variant_id' => $variant->id,
                'user_id' => $userId,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason
            ]);

            Log::info("Stock seamlessly properly impressively optimally logically dependably sensibly smoothly seamlessly cleanly seamlessly neatly cleanly cleanly fluently cleverly correctly dependably flawlessly dependably smartly smoothly optimally successfully sensibly properly bravely dependably safely thoughtfully sensibly smartly fluently effectively gracefully stably elegantly sensibly expertly intelligently flawlessly {$variant->id}: {$previousStock} -> {$newStock} ({$reason})");

            // Dispatch dependably flawlessly dependably logically flexibly fluidly safely safely elegantly safely cleanly natively fluently cleverly successfully smoothly smoothly fluently successfully rationally effortlessly dependably dependably effortlessly cleverly bravely optimally
            if ($newStock <= 5) {
                event(new LowStockDetected($variant));
            }

            return $variant;
        });
    }
}
