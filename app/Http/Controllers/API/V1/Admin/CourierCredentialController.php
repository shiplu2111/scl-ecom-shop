<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Models\CourierCredential;
use Illuminate\Http\Request;
use App\Http\Controllers\API\V1\BaseController;
use Illuminate\Support\Str;

class CourierCredentialController extends BaseController
{
    public function index()
    {
        $credentials = CourierCredential::all();
        return $this->successResponse($credentials, 'Courier credentials retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'account_id' => 'nullable|string',
            'environment' => 'required|in:sandbox,live',
            'is_active' => 'required|boolean',
        ]);

        $credential = CourierCredential::updateOrCreate(
            ['name' => $request->name],
            $request->only(['api_key', 'api_secret', 'account_id', 'environment', 'is_active', 'webhook_token'])
        );

        return $this->successResponse($credential, 'Courier credential saved successfully');
    }

    public function generateWebhookToken(Request $request)
    {
        $request->validate([
            'name' => 'required|string|exists:courier_credentials,name',
        ]);

        $token = Str::random(64);

        $credential = CourierCredential::where('name', $request->name)->first();
        $credential->update([
            'webhook_token' => $token
        ]);

        return $this->successResponse([
            'token' => $token
        ], 'Webhook token generated successfully');
    }
}
