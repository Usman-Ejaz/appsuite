<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Actions\AddOrderItem;
use Domains\Ecommerce\Actions\RemoveOrderItem;
use Domains\Ecommerce\Actions\UpdateOrderItemQuantity;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\OrderItem\CreateRequest;
use Domains\Ecommerce\Http\Requests\OrderItem\UpdateRequest;
use Domains\Ecommerce\Http\Resources\OrderItemResource;
use Domains\Ecommerce\Repositories\OrderItemRepository;
use Domains\Ecommerce\Repositories\OrderRepository;
use Domains\Shared\Models\Product;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
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
    public function list(GetCollectionRequest $request, int $order_id)
    {
        $this->authorize('permission', EcommercePermission::ORDER_VIEW);

        $this->orders->findOrFail($order_id);

        $filters = $request->filters();
        $filters['order_id'] = $order_id;

        $records = $this->items->list($filters);

        return OrderItemResource::collection($records);
    }

    /**
     * Create Order Item
     *
     * Adds a product to an order as a new line item. The product's current name, SKU, and price
     * are captured onto the line item at the time it's added, and its stock quantity is reduced
     * accordingly. Returns an error if the requested quantity exceeds available stock.
     */
    public function create(CreateRequest $request, int $order_id): JsonResponse
    {
        $orderModel = $this->orders->findOrFail($order_id);

        $input = $request->validated();
        $product = Product::findOrFail($input['product_id']);

        $item = $this->addOrderItem->handle($orderModel, $product, $input['quantity']);

        return response()->json(['data' => OrderItemResource::make($item)], 201);
    }

    /**
     * Get Order Item
     *
     * Retrieves a single line item belonging to an order.
     */
    public function get(GetResourceRequest $request, int $order_id, int $id): OrderItemResource
    {
        $this->authorize('permission', EcommercePermission::ORDER_VIEW);

        $this->orders->findOrFail($order_id);

        $filters = $request->filters();
        $filters['order_id'] = $order_id;

        $record = $this->items->get($id, $filters);

        return OrderItemResource::make($record);
    }

    /**
     * Update Order Item
     *
     * Changes the quantity of a line item, adjusting the underlying product's stock by the
     * difference and recalculating the order's totals.
     */
    public function update(UpdateRequest $request, int $order_id, int $id): OrderItemResource
    {
        $this->orders->findOrFail($order_id);

        $item = $this->items->findOrFail($id);

        $item = $this->updateOrderItemQuantity->handle($item, $request->validated('quantity'));

        return OrderItemResource::make($item);
    }

    /**
     * Delete Order Item
     *
     * Removes a line item from an order, restocks its quantity, and recalculates the order's
     * totals.
     */
    public function delete(int $order_id, int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::ORDER_UPDATE);

        $this->orders->findOrFail($order_id);

        $item = $this->items->findOrFail($id);

        $this->removeOrderItem->handle($item);

        return response()->json(null, 204);
    }
}
