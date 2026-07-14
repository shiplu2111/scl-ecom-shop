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
            'base_url' => 'nullable|url',
            'environment' => 'required|in:sandbox,live',
            'secret_key' => 'required|string',
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
            'base_url' => 'nullable|url',
            'environment' => 'sometimes|in:sandbox,live',
            'secret_key' => 'sometimes|string',
            'is_active' => 'boolean',
        ]);

        // Don't overwrite API key when UI sends masked placeholder
        if (array_key_exists('secret_key', $validated)) {
            $key = trim((string) $validated['secret_key']);
            if ($key === '' || $key === '********' || str_starts_with($key, '****')) {
                unset($validated['secret_key']);
            }
        }

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
