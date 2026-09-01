<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository
{
    protected string $model = Order::class;
}
