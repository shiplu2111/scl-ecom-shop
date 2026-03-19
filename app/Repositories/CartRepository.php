<?php

namespace App\Repositories;

use App\Models\Cart;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }
}
