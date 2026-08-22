<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Customer\CreateRequest;
use Domains\Ecommerce\Http\Requests\Customer\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CustomerCollection;
use Domains\Ecommerce\Http\Resources\CustomerResource;
use Domains\Ecommerce\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Customers')]
class CustomerController extends Controller
{
    public function __construct(protected CustomerRepository $customers)
    {
        //
    }

    /**
     * Get Customers
     *
     * Returns a paginated list of customer records for the authenticated company. These are
     * backoffice records for people or businesses who place orders, not accounts the customers
     * can log into themselves.
     */
    public function list(Request $request): CustomerCollection
    {
        $this->authorize('permission', EcommercePermission::CUSTOMER_VIEW->value);

        return new CustomerCollection($this->customers->filter($request->query())->list());
    }

    /**
     * Create Customer
     *
     * Adds a new customer record for the authenticated company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->validated());

        return response()->json(['data' => CustomerResource::make($customer)], 201);
    }

    /**
     * Get Customer
     *
     * Retrieves a single customer record.
     */
    public function get(int $id): CustomerResource
    {
        $this->authorize('permission', EcommercePermission::CUSTOMER_VIEW->value);

        return CustomerResource::make($this->customers->findOrFail($id));
    }

    /**
     * Update Customer
     *
     * Updates an existing customer's details.
     */
    public function update(UpdateRequest $request, int $id): CustomerResource
    {
        return CustomerResource::make($this->customers->update($id, $request->validated()));
    }

    /**
     * Delete Customer
     *
     * Permanently removes a customer record.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::CUSTOMER_DELETE->value);

        $this->customers->delete($id);

        return response()->json(null, 204);
    }
}
