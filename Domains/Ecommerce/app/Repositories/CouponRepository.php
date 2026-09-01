<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Coupon;
use Illuminate\Database\Eloquent\Model;

class CouponRepository extends BaseRepository
{
    protected string $model = Coupon::class;
}
