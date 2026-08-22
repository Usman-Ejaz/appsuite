<?php

namespace Domains\Ecommerce\Http\Requests\Order;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::ORDER_CREATE->value);
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        return [
            /**
             * The customer placing this order.
             *
             * @example 42
             */
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('company_id', $companyId)],

            /**
             * The payment method to use for this order. Can also be set or changed later.
             *
             * @example 3
             */
            'payment_method_id' => ['nullable', 'integer', Rule::exists('payment_methods', 'id')->where('company_id', $companyId)],

            /**
             * The tax amount to apply to the order.
             *
             * @example 12.00
             */
            'tax_total' => ['nullable', 'numeric', 'min:0'],

            /**
             * The shipping cost to apply to the order.
             *
             * @example 9.99
             */
            'shipping_total' => ['nullable', 'numeric', 'min:0'],

            /**
             * The three-letter ISO 4217 currency code for the order's monetary amounts.
             *
             * @example USD
             *
             * @default USD
             */
            'currency' => ['nullable', 'string', 'size:3'],

            /**
             * Freeform notes about the order.
             *
             * @example Please gift wrap.
             */
            'notes' => ['nullable', 'string'],
        ];
    }
}
