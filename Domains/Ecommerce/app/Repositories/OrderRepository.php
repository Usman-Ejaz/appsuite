<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Enums\EcommerceTrigger;
use Domains\Ecommerce\Models\Order;
use Domains\Notification\Services\Triggers\TriggerDispatcher;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository
{
    protected string $model = Order::class;

    /**
     * Create an order and fire `ORDER_CREATED`.
     */
    public function create(array $data): Model
    {
        $order = parent::create($data);

        TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_CREATED)->for($order);

        return $order;
    }

    /**
     * Update an order, firing `ORDER_STATUS_CHANGED` when its status moves.
     */
    public function update($id, array $data): Model
    {
        $previousStatus = $this->findOrFail($id)->status;

        $order = parent::update($id, $data);

        if ($order->status !== $previousStatus) {
            TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_STATUS_CHANGED)
                ->for($order)
                ->with(['previous' => ['order.status' => $previousStatus?->value]]);
        }

        return $order;
    }
}
