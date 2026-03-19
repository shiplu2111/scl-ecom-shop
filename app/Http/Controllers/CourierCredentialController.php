<?php

namespace App\Http\Controllers;

use App\Models\CourierCredential;
use Illuminate\Http\Request;

class CourierCredentialController extends \App\Http\Controllers\API\V1\BaseController
{
    public function index()
    {
        $credentials = CourierCredential::all();
        return $this->successResponse($credentials, 'Courier credentials retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:courier_credentials,id',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'account_id' => 'nullable|string',
            'environment' => 'required|in:sandbox,live',
            'is_active' => 'required|boolean',
        ]);

        $credential = CourierCredential::findOrFail($request->id);
        $credential->update($request->only(['api_key', 'api_secret', 'account_id', 'environment', 'is_active']));

        return $this->successResponse($credential, 'Courier credential updated successfully');
    }
}
