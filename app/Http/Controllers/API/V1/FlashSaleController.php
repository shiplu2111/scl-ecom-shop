<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlashSale;

class FlashSaleController extends Controller
{
    public function active()
    {
        // Find the latest flash sale where is_active is true (ignoring time for display)
        $activeSale = FlashSale::where('is_active', true)
            ->with(['items' => function($query) {
                $query->with(['product.images', 'product.brand', 'variant']);
            }])
            ->latest()
            ->first();

        // Fallback to absolute latest if no marked active sale exists
        if (!$activeSale) {
            $activeSale = FlashSale::with(['items' => function($query) {
                $query->with(['product.images', 'product.brand', 'variant']);
            }])->latest()->first();
        }

        if (!$activeSale) {
            return response()->json([
                'status' => false,
                'message' => 'No flash sale records found.',
                'data' => null
            ]);
        }

        // Add currently_active flag for frontend logic flawlessly properly
        $now = now();
        $isCurrentlyActive = $activeSale->is_active && 
                             $activeSale->start_time <= $now && 
                             $activeSale->end_time >= $now;
        
        $activeSale->is_currently_active = $isCurrentlyActive;
        // Include start and end status codes for clarity flawlessly properly
        $activeSale->is_upcoming = $activeSale->start_time > $now;
        $activeSale->is_finished = $activeSale->end_time < $now;

        return response()->json([
            'status' => true,
            'message' => 'Flash sale retrieved flawlessly.',
            'data' => $activeSale
        ]);
    }

    public function featured()
    {
        $featuredSale = FlashSale::where('is_active', true)
            ->where('is_featured', true)
            ->with(['items' => function($query) {
                $query->with(['product.images', 'product.brand', 'variant']);
            }])
            ->latest()
            ->first();

        if (!$featuredSale) {
            return $this->active();
        }

        // Add currently_active flag for frontend logic flawlessly properly
        $now = now();
        $isCurrentlyActive = $featuredSale->is_active && 
                             $featuredSale->start_time <= $now && 
                             $featuredSale->end_time >= $now;
        
        $featuredSale->is_currently_active = $isCurrentlyActive;
        // Include start and end status codes for clarity flawlessly properly
        $featuredSale->is_upcoming = $featuredSale->start_time > $now;
        $featuredSale->is_finished = $featuredSale->end_time < $now;

        return response()->json([
            'status' => true,
            'message' => 'Featured flash sale retrieved flawlessly.',
            'data' => $featuredSale
        ]);
    }
}
