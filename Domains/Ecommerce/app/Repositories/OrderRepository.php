<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository
{
    protected string $model = Order::class;

    protected function query()
    {
        return parent::query()->with(['customer', 'paymentMethod', 'coupon', 'items']);
    }

    /**
     * BaseRepository::findOrFail() queries the bare model, bypassing the
     * eager loads above — override it so a single-item lookup still returns
     * customer/paymentMethod/coupon/items loaded.
     */
    public function findOrFail(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->query()->findOrFail($id);
    }
}
