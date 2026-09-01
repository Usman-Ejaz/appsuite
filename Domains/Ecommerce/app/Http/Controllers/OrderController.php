<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Actions\ApplyCouponToOrder;
use Domains\Ecommerce\Actions\CancelOrder;
use Domains\Ecommerce\Actions\RemoveCouponFromOrder;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Order\ApplyCouponRequest;
use Domains\Ecommerce\Http\Requests\Order\CreateRequest;
use Domains\Ecommerce\Http\Requests\Order\UpdateRequest;
use Domains\Ecommerce\Http\Resources\OrderCollection;
use Domains\Ecommerce\Http\Resources\OrderResource;
use Domains\Ecommerce\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
class OrderController extends Controller
{
    public function __construct(
        protected OrderRepository $orders,
        protected ApplyCouponToOrder $applyCouponToOrder,
        protected RemoveCouponFromOrder $removeCouponFromOrder,
        protected CancelOrder $cancelOrder,
    ) {
        //
    }

    /**
     * Get Orders
     *
     * Returns a paginated list of orders for the authenticated company.
     */
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', EcommercePermission::ORDER_VIEW);

        $filters = $request->filters();

        $records = $this->orders->list($filters);

        return OrderResource::collection($records);
    }

    /**
     * Create Order
     *
     * Creates a new order for a customer. The order starts in the `Pending` state with zero
     * totals; add line items afterward through the order items endpoints to build it up.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $order = $this->orders->create($input);

        return response()->json(['data' => OrderResource::make($order)], 201);
    }

    /**
     * Get Order
     *
     * Retrieves a single order, including its customer, payment method, applied coupon, and
     * line items.
     */
    public function get(GetResourceRequest $request, int $id): OrderResource
    {
        $this->authorize('permission', EcommercePermission::ORDER_VIEW);

        $filters = $request->filters();

        $record = $this->orders->get($id, $filters);

        return OrderResource::make($record);
    }

    /**
     * Update Order
     *
     * Updates an order's payment method, status, totals, or notes.
     */
    public function update(UpdateRequest $request, int $id): OrderResource
    {
        $input = $request->validated();

        $record = $this->orders->update($id, $input);

        return OrderResource::make($record);
    }

    /**
     * Delete Order
     *
     * Permanently removes an order.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::ORDER_DELETE);

        $this->orders->delete($id);

        return response()->json(null, 204);
    }

    /**
     * Cancel Order
     *
     * Cancels an order: restocks the quantity of every line item and reverses any applied
     * coupon's usage count. Returns an error if the order is already cancelled.
     */
    public function cancel(int $id): OrderResource
    {
        $this->authorize('permission', EcommercePermission::ORDER_CANCEL);

        return OrderResource::make($this->cancelOrder->handle($this->orders->findOrFail($id)));
    }

    /**
     * Apply Order Coupon
     *
     * Applies a coupon code to the order and recalculates its totals. Returns an error if the
     * coupon doesn't exist, is inactive, has expired, has reached its usage limit, or if the
     * order's subtotal doesn't meet the coupon's minimum amount.
     */
    public function applyCoupon(ApplyCouponRequest $request, int $id): OrderResource
    {
        $order = $this->applyCouponToOrder->handle($this->orders->findOrFail($id), $request->validated('code'));

        return OrderResource::make($order);
    }

    /**
     * Remove Order Coupon
     *
     * Removes any coupon applied to the order, zeroes the discount, reverses the coupon's usage
     * count, and recalculates the order's totals.
     */
    public function removeCoupon(int $id): OrderResource
    {
        $this->authorize('permission', EcommercePermission::ORDER_APPLY_COUPON);

        return OrderResource::make($this->removeCouponFromOrder->handle($this->orders->findOrFail($id)));
    }
}
