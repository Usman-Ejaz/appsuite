<?php

namespace Domains\Ecommerce\Http\Requests\PaymentMethod;

use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Shared\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::PAYMENT_METHOD_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The name shown for this payment method at checkout.
             *
             * @example Cash on Delivery
             */
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('payment_methods')->where('company_id', $this->user()?->getCompanyId())->ignore($this->route('id'))],

            /**
             * The kind of payment method this is.
             *
             * @example Cash
             */
            'type' => ['sometimes', Rule::enum(PaymentMethodType::class)],

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
             * @example false
             */
            'is_default' => ['nullable', 'boolean'],

            /**
             * Whether this payment method can currently be offered at checkout.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
