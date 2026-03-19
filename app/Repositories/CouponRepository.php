<?php

namespace App\Repositories;

use App\Models\Coupon;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }
}
