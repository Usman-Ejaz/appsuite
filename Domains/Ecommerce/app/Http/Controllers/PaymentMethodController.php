<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\PaymentMethod\CreateRequest;
use Domains\Ecommerce\Http\Requests\PaymentMethod\UpdateRequest;
use Domains\Ecommerce\Http\Resources\PaymentMethodCollection;
use Domains\Ecommerce\Http\Resources\PaymentMethodResource;
use Domains\Ecommerce\Repositories\PaymentMethodRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
class PaymentMethodController extends Controller
{
    public function __construct(protected PaymentMethodRepository $paymentMethods)
    {
        //
    }

    /**
     * Get Payment Methods
     *
     * Returns a paginated list of payment methods for the authenticated company.
     */
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', EcommercePermission::PAYMENT_METHOD_VIEW);

        $filters = $request->filters();

        $records = $this->paymentMethods->list($filters);

        return PaymentMethodResource::collection($records);
    }

    /**
     * Create Payment Method
     *
     * Creates a new checkout payment method for the authenticated company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $record = $this->paymentMethods->create($input);

        return response()->json(['data' => PaymentMethodResource::make($record)], 201);
    }

    /**
     * Get Payment Method
     *
     * Retrieves a single payment method.
     */
    public function get(GetResourceRequest $request, int $id): PaymentMethodResource
    {
        $this->authorize('permission', EcommercePermission::PAYMENT_METHOD_VIEW);

        $filters = $request->filters();

        $record = $this->paymentMethods->get($id, $filters);

        return PaymentMethodResource::make($record);
    }

    /**
     * Update Payment Method
     *
     * Updates a payment method's name, type, provider, instructions, or its default and
     * active status.
     */
    public function update(UpdateRequest $request, int $id): PaymentMethodResource
    {
        $input = $request->validated();

        $record = $this->paymentMethods->update($id, $input);

        return PaymentMethodResource::make($record);
    }

    /**
     * Delete Payment Method
     *
     * Permanently removes a payment method.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::PAYMENT_METHOD_DELETE);

        $this->paymentMethods->delete($id);

        return response()->json(null, 204);
    }
}
