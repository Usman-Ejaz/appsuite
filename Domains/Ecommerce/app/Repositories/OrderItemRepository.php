<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\OrderItem;

class OrderItemRepository extends BaseRepository
{
    protected string $model = OrderItem::class;
}
