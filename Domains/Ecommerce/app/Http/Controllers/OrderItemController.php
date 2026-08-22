<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Actions\AddOrderItem;
use Domains\Ecommerce\Actions\RemoveOrderItem;
use Domains\Ecommerce\Actions\UpdateOrderItemQuantity;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\OrderItem\CreateRequest;
use Domains\Ecommerce\Http\Requests\OrderItem\UpdateRequest;
use Domains\Ecommerce\Http\Resources\OrderItemCollection;
use Domains\Ecommerce\Http\Resources\OrderItemResource;
use Domains\Ecommerce\Models\EcommerceProduct;
use Domains\Ecommerce\Repositories\OrderItemRepository;
use Domains\Ecommerce\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce, Order Items')]
class OrderItemController extends Controller
{
    public function __construct(
        protected OrderRepository $orders,
        protected OrderItemRepository $items,
        protected AddOrderItem $addOrderItem,
        protected UpdateOrderItemQuantity $updateOrderItemQuantity,
        protected RemoveOrderItem $removeOrderItem,
    ) {
        //
    }

    /**
     * Get Order Items
     *
     * Returns the line items belonging to an order.
     */
    public function list(int $order): OrderItemCollection
    {
        $this->authorize('permission', EcommercePermission::ORDER_VIEW->value);
        $this->orders->findOrFail($order);

        return new OrderItemCollection($this->items->filter(['order_id' => $order])->list());
    }

    /**
     * Create Order Item
     *
     * Adds a product to an order as a new line item. The product's current name, SKU, and price
     * are captured onto the line item at the time it's added, and its stock quantity is reduced
     * accordingly. Returns an error if the requested quantity exceeds available stock.
     */
    public function create(CreateRequest $request, int $order): JsonResponse
    {
        $orderModel = $this->orders->findOrFail($order);
        $product = EcommerceProduct::findOrFail($request->validated('ecommerce_product_id'));

        $item = $this->addOrderItem->handle($orderModel, $product, $request->validated('quantity'));

        return response()->json(['data' => OrderItemResource::make($item)], 201);
    }

    /**
     * Get Order Item
     *
     * Retrieves a single line item belonging to an order.
     */
    public function get(int $order, int $id): OrderItemResource
    {
        $this->authorize('permission', EcommercePermission::ORDER_VIEW->value);
        $this->orders->findOrFail($order);

        return OrderItemResource::make($this->items->filter(['order_id' => $order])->findOrFail($id));
    }

    /**
     * Update Order Item
     *
     * Changes the quantity of a line item, adjusting the underlying product's stock by the
     * difference and recalculating the order's totals.
     */
    public function update(UpdateRequest $request, int $order, int $id): OrderItemResource
    {
        $this->orders->findOrFail($order);
        $item = $this->items->filter(['order_id' => $order])->findOrFail($id);

        $item = $this->updateOrderItemQuantity->handle($item, $request->validated('quantity'));

        return OrderItemResource::make($item);
    }

    /**
     * Delete Order Item
     *
     * Removes a line item from an order, restocks its quantity, and recalculates the order's
     * totals.
     */
    public function delete(int $order, int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::ORDER_UPDATE->value);
        $this->orders->findOrFail($order);
        $item = $this->items->filter(['order_id' => $order])->findOrFail($id);

        $this->removeOrderItem->handle($item);

        return response()->json(null, 204);
    }
}
