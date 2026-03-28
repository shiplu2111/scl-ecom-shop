<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Admin
 * @subgroup FAQ Management
 */
class FaqController extends BaseController
{
    /**
     * List all FAQs.
     */
    public function index()
    {
        $faqs = Faq::orderBy('order')->get();
        return $this->successResponse($faqs, 'FAQs retrieved successfully');
    }

    /**
     * Store a new FAQ.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'order' => 'integer',
            'status' => 'required|in:Published,Draft',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $faq = Faq::create($request->all());

        return $this->successResponse($faq, 'FAQ created successfully', 201);
    }

    /**
     * Update an existing FAQ.
     */
    public function update(Request $request, Faq $faq)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'nullable|string|max:255',
            'answer' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'order' => 'integer',
            'status' => 'nullable|in:Published,Draft',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $faq->update($request->all());

        return $this->successResponse($faq, 'FAQ updated successfully');
    }

    /**
     * Delete an FAQ.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        return $this->successResponse(null, 'FAQ deleted successfully');
    }

    /**
     * Bulk delete FAQs.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:faqs,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        Faq::whereIn('id', $request->ids)->delete();

        return $this->successResponse(null, 'FAQs deleted successfully');
    }

    /**
     * Reorder FAQs.
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:faqs,id',
            'orders.*.order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        foreach ($request->orders as $orderData) {
            Faq::where('id', $orderData['id'])->update(['order' => $orderData['order']]);
        }

        return $this->successResponse(null, 'FAQs reordered successfully');
    }
}
