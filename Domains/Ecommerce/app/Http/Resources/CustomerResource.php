<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Identity\Models\Customer;
use Illuminate\Http\Request;

/**
 * @mixin Customer
 */
class CustomerResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.customers';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,

            /**
             * Whether this is an individual or a business customer.
             */
            'type' => $this->type,

            'name' => $this->name,

            /**
             * The business's name, set when this is a business customer.
             */
            'business_name' => $this->business_name,

            'email' => $this->email,
            'phone' => $this->phone,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,

            /**
             * The two-letter ISO 3166-1 country code for the customer's address.
             *
             * @example US
             */
            'country' => $this->country,

            'notes' => $this->notes,
            'is_active' => $this->is_active,
        ]);
    }
}
