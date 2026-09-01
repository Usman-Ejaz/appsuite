<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class OrderItemRepository extends BaseRepository
{
    protected string $model = OrderItem::class;
}
