<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Services\CouponService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Coupon
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
        $coupons = $this->couponService->all();
        return $this->successResponse(CouponResource::collection($coupons), 'Coupons fetched successfully');
    }

    public function store(StoreCouponRequest $request)
    {
        $coupon = $this->couponService->createCoupon($request->validated());
        return $this->successResponse(new CouponResource($coupon), 'Coupon created successfully', 201);
    }

    public function show($id)
    {
        $coupon = $this->couponService->find($id);
        return $this->successResponse(new CouponResource($coupon), 'Coupon details fetched successfully');
    }

    public function update(UpdateCouponRequest $request, $id)
    {
        $coupon = $this->couponService->updateCoupon($id, $request->validated());
        return $this->successResponse(new CouponResource($coupon), 'Coupon updated successfully');
    }

    public function destroy($id)
    {
        $this->couponService->deleteCoupon($id);
        return $this->successResponse([], 'Coupon deleted successfully');
    }
}
