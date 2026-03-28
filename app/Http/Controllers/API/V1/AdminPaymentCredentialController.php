<?php

namespace App\Http\Controllers\API\V1;

use App\Models\PaymentCredential;
use App\Services\PaymentGatewayConfigService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Admin
 * @subgroup Payment Configuration
 */
class AdminPaymentCredentialController extends BaseController
{
    protected PaymentGatewayConfigService $configService;

    public function __construct(PaymentGatewayConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function index()
    {
        $credentials = $this->configService->getAllGateways();
        return $this->successResponse($credentials, 'Payment credentials fetched successfully');
    }

    public function store(Request $request)
    {
        $allowedGateways = ['UddoktaPay', 'SSLCommerz', 'Stripe', 'PayPal'];

        $validated = $request->validate([
            'name' => ['required', 'string', Rule::in($allowedGateways)],
            'environment' => 'required|in:sandbox,live',
            'merchant_id' => 'required|string',
            'secret_key' => 'required|string',
            'callback_url' => 'nullable|url',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $credential = PaymentCredential::updateOrCreate(
            ['name' => $validated['name']],
            $validated
        );

        return $this->successResponse($credential, 'Payment credential processed successfully');
    }

    public function update(Request $request, $id)
    {
        $credential = PaymentCredential::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'environment' => 'sometimes|in:sandbox,live',
            'merchant_id' => 'sometimes|string',
            'secret_key' => 'sometimes|string',
            'callback_url' => 'nullable|url',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $credential->update($validated);

        return $this->successResponse($credential, 'Payment credential updated successfully');
    }

    public function destroy($id)
    {
        $credential = PaymentCredential::findOrFail($id);
        $credential->delete();

        return $this->successResponse(null, 'Payment credential deleted successfully');
    }
}
