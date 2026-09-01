<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Coupon\CreateRequest;
use Domains\Ecommerce\Http\Requests\Coupon\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CouponResource;
use Domains\Ecommerce\Repositories\CouponRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
class CouponController extends Controller
{
    public function __construct(protected CouponRepository $coupons)
    {
        //
    }

    /**
     * Get Coupons
     *
     * Returns a paginated list of coupons for the authenticated company.
     */
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', EcommercePermission::COUPON_VIEW);

        $filters = $request->filters();

        $records = $this->coupons->list($filters);

        return CouponResource::collection($records);
    }

    /**
     * Create Coupon
     *
     * Creates a new coupon for the authenticated company. Its usage count starts at zero.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $coupon = $this->coupons->create($input);

        return response()->json(['data' => CouponResource::make($coupon)], 201);
    }

    /**
     * Get Coupon
     *
     * Retrieves a single coupon, including the campaign it belongs to, if one is linked.
     */
    public function get(GetResourceRequest $request, int $id): CouponResource
    {
        $this->authorize('permission', EcommercePermission::COUPON_VIEW);

        $filters = $request->filters();

        $record = $this->coupons->get($id, $filters);

        return CouponResource::make($record);
    }

    /**
     * Update Coupon
     *
     * Updates a coupon's code, discount configuration, usage limit, active window, or
     * campaign link.
     */
    public function update(UpdateRequest $request, int $id): CouponResource
    {
        $input = $request->validated();

        $record = $this->coupons->update($id, $input);

        return CouponResource::make($record);
    }

    /**
     * Delete Coupon
     *
     * Permanently removes a coupon.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::COUPON_DELETE);

        $this->coupons->delete($id);

        return response()->json(null, 204);
    }
}
