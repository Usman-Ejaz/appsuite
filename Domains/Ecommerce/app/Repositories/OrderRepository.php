<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Order;

class OrderRepository extends BaseRepository
{
    protected string $model = Order::class;
}
