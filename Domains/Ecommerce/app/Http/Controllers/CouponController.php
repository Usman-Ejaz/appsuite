<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Coupon\CreateRequest;
use Domains\Ecommerce\Http\Requests\Coupon\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CouponCollection;
use Domains\Ecommerce\Http\Resources\CouponResource;
use Domains\Ecommerce\Repositories\CouponRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Coupons')]
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
    public function list(Request $request): CouponCollection
    {
        $this->authorize('permission', EcommercePermission::COUPON_VIEW->value);

        return new CouponCollection($this->coupons->filter($request->query())->list());
    }

    /**
     * Create Coupon
     *
     * Creates a new coupon for the authenticated company. Its usage count starts at zero.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $coupon = $this->coupons->create($request->validated());

        return response()->json(['data' => CouponResource::make($coupon)], 201);
    }

    /**
     * Get Coupon
     *
     * Retrieves a single coupon, including the campaign it belongs to, if one is linked.
     */
    public function get(int $id): CouponResource
    {
        $this->authorize('permission', EcommercePermission::COUPON_VIEW->value);

        return CouponResource::make($this->coupons->findOrFail($id));
    }

    /**
     * Update Coupon
     *
     * Updates a coupon's code, discount configuration, usage limit, active window, or
     * campaign link.
     */
    public function update(UpdateRequest $request, int $id): CouponResource
    {
        return CouponResource::make($this->coupons->update($id, $request->validated()));
    }

    /**
     * Delete Coupon
     *
     * Permanently removes a coupon.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::COUPON_DELETE->value);

        $this->coupons->delete($id);

        return response()->json(null, 204);
    }
}
