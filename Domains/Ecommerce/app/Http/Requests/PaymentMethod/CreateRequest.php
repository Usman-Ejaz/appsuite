<?php

namespace Domains\Ecommerce\Http\Requests\PaymentMethod;

use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Shared\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::PAYMENT_METHOD_CREATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The name shown for this payment method at checkout.
             *
             * @example Cash on Delivery
             */
            'name' => ['required', 'string', 'max:255', Rule::unique('payment_methods')->where('company_id', $this->user()?->getCompanyId())],

            /**
             * The kind of payment method this is.
             *
             * @example Cash
             */
            'type' => ['required', Rule::enum(PaymentMethodType::class)],

            /**
             * The processor or bank behind this payment method. Only meaningful for some
             * types, such as `Card` or `Bank Transfer`.
             *
             * @example Stripe
             */
            'provider' => ['nullable', 'string', 'max:255'],

            /**
             * Freeform text shown to the customer at checkout, such as bank account details
             * for a `Bank Transfer` method.
             *
             * @example Transfer to account 1234567890 at HBL and upload the receipt.
             */
            'instructions' => ['nullable', 'string'],

            /**
             * Whether this is the company's preferred payment method at checkout.
             *
             * @default false
             */
            'is_default' => ['nullable', 'boolean'],

            /**
             * Whether this payment method can currently be offered at checkout.
             *
             * @default true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
