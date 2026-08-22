<?php

namespace Domains\Ecommerce\Http\Requests\Customer;

use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Identity\Enums\CustomerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::CUSTOMER_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * Whether this is an individual or a business customer.
             *
             * @example Business
             */
            'type' => ['nullable', Rule::enum(CustomerType::class)],

            /**
             * The customer's name, or the primary contact's name if this is a business.
             *
             * @example Jane Cooper
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * The business's name, typically set when `type` is `Business`.
             *
             * @example Acme Corp
             */
            'business_name' => ['nullable', 'string', 'max:255'],

            /**
             * The customer's email address. Must be unique among the company's other customers.
             *
             * @example jane@example.com
             */
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('customers')->where('company_id', $this->user()?->getCompanyId())->ignore($this->route('id'))],

            /**
             * The customer's phone number.
             *
             * @example +1 555-201-4433
             */
            'phone' => ['nullable', 'string', 'max:255'],

            /**
             * The first line of the customer's address.
             *
             * @example 500 Market Street
             */
            'address_line_1' => ['nullable', 'string', 'max:255'],

            /**
             * The second line of the customer's address, such as a suite or unit number.
             *
             * @example Suite 200
             */
            'address_line_2' => ['nullable', 'string', 'max:255'],

            /**
             * The customer's city.
             *
             * @example San Francisco
             */
            'city' => ['nullable', 'string', 'max:255'],

            /**
             * The customer's state or province.
             *
             * @example CA
             */
            'state' => ['nullable', 'string', 'max:255'],

            /**
             * The customer's postal or zip code.
             *
             * @example 94105
             */
            'postal_code' => ['nullable', 'string', 'max:255'],

            /**
             * The two-letter ISO 3166-1 country code for the customer's address.
             *
             * @example US
             */
            'country' => ['nullable', 'string', 'size:2'],

            /**
             * Freeform notes about the customer.
             *
             * @example VIP customer, prefers email contact.
             */
            'notes' => ['nullable', 'string'],

            /**
             * Whether the customer is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
