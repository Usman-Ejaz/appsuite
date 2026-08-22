<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\PaymentMethod\CreateRequest;
use Domains\Ecommerce\Http\Requests\PaymentMethod\UpdateRequest;
use Domains\Ecommerce\Http\Resources\PaymentMethodCollection;
use Domains\Ecommerce\Http\Resources\PaymentMethodResource;
use Domains\Ecommerce\Repositories\PaymentMethodRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Payment Methods')]
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
    public function list(Request $request): PaymentMethodCollection
    {
        $this->authorize('permission', EcommercePermission::PAYMENT_METHOD_VIEW->value);

        return new PaymentMethodCollection($this->paymentMethods->filter($request->query())->list());
    }

    /**
     * Create Payment Method
     *
     * Creates a new checkout payment method for the authenticated company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $paymentMethod = $this->paymentMethods->create($request->validated());

        return response()->json(['data' => PaymentMethodResource::make($paymentMethod)], 201);
    }

    /**
     * Get Payment Method
     *
     * Retrieves a single payment method.
     */
    public function get(int $id): PaymentMethodResource
    {
        $this->authorize('permission', EcommercePermission::PAYMENT_METHOD_VIEW->value);

        return PaymentMethodResource::make($this->paymentMethods->findOrFail($id));
    }

    /**
     * Update Payment Method
     *
     * Updates a payment method's name, type, provider, instructions, or its default and
     * active status.
     */
    public function update(UpdateRequest $request, int $id): PaymentMethodResource
    {
        return PaymentMethodResource::make($this->paymentMethods->update($id, $request->validated()));
    }

    /**
     * Delete Payment Method
     *
     * Permanently removes a payment method.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::PAYMENT_METHOD_DELETE->value);

        $this->paymentMethods->delete($id);

        return response()->json(null, 204);
    }
}
