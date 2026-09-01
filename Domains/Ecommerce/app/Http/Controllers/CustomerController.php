<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Customer\CreateRequest;
use Domains\Ecommerce\Http\Requests\Customer\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CustomerCollection;
use Domains\Ecommerce\Http\Resources\CustomerResource;
use Domains\Ecommerce\Repositories\CustomerRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
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
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', EcommercePermission::CUSTOMER_VIEW);

        $filters = $request->filters();

        $records = $this->customers->list($filters);

        return CustomerResource::collection($records);
    }

    /**
     * Create Customer
     *
     * Adds a new customer record for the authenticated company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $customer = $this->customers->create($input);

        return response()->json(['data' => CustomerResource::make($customer)], 201);
    }

    /**
     * Get Customer
     *
     * Retrieves a single customer record.
     */
    public function get(GetResourceRequest $request, int $id): CustomerResource
    {
        $this->authorize('permission', EcommercePermission::CUSTOMER_VIEW);

        $filters = $request->filters();

        $record = $this->customers->get($id, $filters);

        return CustomerResource::make($record);
    }

    /**
     * Update Customer
     *
     * Updates an existing customer's details.
     */
    public function update(UpdateRequest $request, int $id): CustomerResource
    {
        $input = $request->validated();

        $record = $this->customers->update($id, $input);

        return CustomerResource::make($record);
    }

    /**
     * Delete Customer
     *
     * Permanently removes a customer record.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::CUSTOMER_DELETE);

        $this->customers->delete($id);

        return response()->json(null, 204);
    }
}
