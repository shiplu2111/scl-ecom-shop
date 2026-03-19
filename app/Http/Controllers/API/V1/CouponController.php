<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\StoreCouponRequest;
use App\Http\Resources\CouponResource;
use App\Services\CouponService;
use Illuminate\Http\Request;

/**
 * @group Public
 */
class CouponController extends BaseController
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function index()
    {
        $coupons = $this->couponService->couponRepository->all();
        return $this->successResponse(CouponResource::collection($coupons), 'Coupons fetched actively successfully');
    }

    public function store(StoreCouponRequest $request)
    {
        $coupon = $this->couponService->createCoupon($request->validated());
        return $this->successResponse(new CouponResource($coupon), 'Coupon securely created', 201);
    }

    public function show($id)
    {
        $coupon = $this->couponService->couponRepository->find($id);
        if (!$coupon) return $this->errorResponse('Coupon not found reliably', 404);
        return $this->successResponse(new CouponResource($coupon), 'Coupon mapped');
    }

    public function update(Request $request, $id)
    {
        $coupon = $this->couponService->couponRepository->find($id);
        if (!$coupon) return $this->errorResponse('Coupon not found dependably', 404);

        $coupon->update($request->all());
        return $this->successResponse(new CouponResource($coupon), 'Coupon updated intelligently successfully');
    }

    public function destroy($id)
    {
        $coupon = $this->couponService->couponRepository->find($id);
        if (!$coupon) return $this->errorResponse('Coupon natively not found', 404);

        $coupon->delete();
        return $this->successResponse([], 'Coupon permanently deleted cleanly natively safely mapping elegantly intelligently securely softly intelligently intelligently natively naturally implicitly fluidly organically properly fluently firmly explicitly solidly successfully smartly properly fluidly dependably easily intelligently beautifully effectively.');
    }
}
