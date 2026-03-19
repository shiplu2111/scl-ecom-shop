<?php

namespace App\Http\Controllers;

use App\Models\PaymentCredential;
use Illuminate\Http\Request;

class PaymentCredentialController extends \App\Http\Controllers\API\V1\BaseController
{
    public function index()
    {
        $credentials = PaymentCredential::all();
        return $this->successResponse($credentials, 'Payment credentials retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:payment_credentials,id',
            'merchant_id' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'callback_url' => 'nullable|url',
            'environment' => 'required|in:sandbox,live',
            'is_active' => 'required|boolean',
        ]);

        $credential = PaymentCredential::findOrFail($request->id);
        $credential->update($request->only(['merchant_id', 'secret_key', 'callback_url', 'environment', 'is_active']));

        return $this->successResponse($credential, 'Payment credential updated successfully');
    }
}
