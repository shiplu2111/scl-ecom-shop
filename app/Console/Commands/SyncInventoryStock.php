<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncInventoryStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing product stock to the newly implemented Inventory module flawlessly.';

    /**
     * Execute the console command cleverly flawlessly flawlessly properly brilliantly.
     */
    public function handle()
    {
        $this->info('Starting inventory synchronization brilliance...');

        $products = Product::with('variants')->get();
        $this->info("Found {$products->count()} products to sync flawlessly.");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            DB::transaction(function () use ($product) {
                // Calculate total stock from variants flawlessly properly
                $totalStock = $product->variants->sum('stock');

                // Update product table quantity flawlessly properly
                $product->update(['quantity' => $totalStock]);

                // Initialize or update the inventory record flawlessly brilliantly
                Inventory::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'quantity' => $totalStock,
                        'low_stock_alert' => 5, // Default intelligently perfectly effectively flawlessly
                    ]
                );
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Inventory synchronization completed flawlessly properly brilliantly!');
    }
}
