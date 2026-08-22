<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class OrderItemRepository extends BaseRepository
{
    protected string $model = OrderItem::class;

    protected function query()
    {
        $query = parent::query()->with('ecommerceProduct');

        if ($orderId = Arr::get($this->filter, 'order_id')) {
            $query->where('order_id', $orderId);
        }

        return $query;
    }

    /**
     * BaseRepository::findOrFail() queries the bare model, bypassing both
     * the eager load above AND the order_id filter — without this override,
     * an item id from a different order (same company) would still resolve
     * under this order's nested route.
     */
    public function findOrFail(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->query()->findOrFail($id);
    }
}
