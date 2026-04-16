<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;

class InvoiceController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Download Invoice PDF for User.
     */
    public function downloadUserInvoice($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['items.product', 'items.productVariant', 'shippingAddress', 'user'])
            ->findOrFail($id);

        return $this->generatePdf($order);
    }

    /**
     * Download Invoice PDF for Admin.
     */
    public function downloadAdminInvoice($id)
    {
        $order = Order::with(['items.product', 'items.productVariant', 'shippingAddress', 'user'])
            ->findOrFail($id);

        return $this->generatePdf($order);
    }

    /**
     * Internal PDF Generation logic.
     */
    protected function generatePdf(Order $order)
    {
        // Fetch branding and currency settings
        $settings = \App\Models\Setting::whereIn('group', ['site', 'currency'])
            ->pluck('value', 'key')
            ->toArray();
        // Generate PDF using mPDF
        $pdf = Pdf::loadView('emails.order-invoice', [
            'order' => $order,
            'site_settings' => $settings
        ], [], [
            'mode' => 'utf-8', 
            'format' => 'A4',
            'default_font' => 'DejaVuSans',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
        ]);

        $content = $pdf->output();
        
        $filename = "Invoice-" . $order->order_number . ".pdf";
        
        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Access-Control-Allow-Origin', request()->header('Origin') ?: 'http://localhost:3001')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Vary', 'Origin');
    }
}
