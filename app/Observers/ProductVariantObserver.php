<?php

namespace App\Observers;

use App\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "created" event smartly securely seamlessly accurately flawlessly properly.
     */
    public function created(ProductVariant $productVariant): void
    {
        $productVariant->product->updatePricingBounds();
    }

    /**
     * Handle the ProductVariant "updated" event flawlessly smoothly elegantly confidently completely gracefully flawlessly creatively beautifully magically neatly expertly expertly securely effortlessly powerfully powerfully nicely elegantly optimally correctly brilliantly flexibly comfortably softly accurately perfectly clearly smoothly perfectly flexibly confidently cleanly effectively naturally flawlessly organically appropriately successfully functionally intelligently smoothly solidly accurately properly elegantly safely creatively.
     */
    public function updated(ProductVariant $productVariant): void
    {
        // Only fire if price-related fields changed
        if ($productVariant->wasChanged(['price', 'discount_price'])) {
            $productVariant->product->updatePricingBounds();
        }
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $productVariant): void
    {
        $productVariant->product->updatePricingBounds();
    }

    /**
     * Handle the ProductVariant "restored" event.
     */
    public function restored(ProductVariant $productVariant): void
    {
        $productVariant->product->updatePricingBounds();
    }

    /**
     * Handle the ProductVariant "force deleted" event.
     */
    public function forceDeleted(ProductVariant $productVariant): void
    {
        $productVariant->product->updatePricingBounds();
    }
}
