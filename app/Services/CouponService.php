<?php

namespace App\Services;

use App\Repositories\CouponRepositoryInterface;

class CouponService extends BaseService
{
    protected CouponRepositoryInterface $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    public function all()
    {
        return $this->couponRepository->all();
    }

    public function find(int $id)
    {
        return $this->couponRepository->find($id);
    }

    public function createCoupon(array $data)
    {
        return $this->couponRepository->create($data);
    }

    public function updateCoupon(int $id, array $data)
    {
        return $this->couponRepository->update($id, $data);
    }

    public function deleteCoupon(int $id)
    {
        return $this->couponRepository->delete($id);
    }

    public function getCouponByCode(string $code)
    {
        return $this->couponRepository->getModel()->where('code', $code)->first();
    }

    public function validateForCart($coupon, float $cartSubtotal)
    {
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon not found.'];
        }

        if (!$coupon->is_active) {
            return ['valid' => false, 'message' => 'Coupon is inactive.'];
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'Coupon has expired.'];
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached.'];
        }

        if ($cartSubtotal < $coupon->min_cart_amount) {
            return ['valid' => false, 'message' => 'Cart minimum amount not met for this coupon.'];
        }

        return ['valid' => true];
    }
}
