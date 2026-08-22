<?php

namespace Domains\Ecommerce\Http\Requests\Order;

use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::ORDER_UPDATE->value);
    }

    /**
     * `Cancelled` is deliberately excluded — cancelling has real side
     * effects (restocking, coupon usage rollback) and must go through the
     * dedicated cancel action/permission, not a plain status write.
     */
    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        return [
            /**
             * The payment method to use for this order.
             *
             * @example 3
             */
            'payment_method_id' => ['nullable', 'integer', Rule::exists('payment_methods', 'id')->where('company_id', $companyId)],

            /**
             * The order's lifecycle state. Cannot be set to `Cancelled` here; use the cancel endpoint instead, since cancelling also restocks items and reverses any applied coupon's usage.
             *
             * @example Processing
             */
            'status' => ['nullable', Rule::enum(OrderStatus::class)->except([OrderStatus::CANCELLED])],

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
             * Freeform notes about the order.
             *
             * @example Please gift wrap.
             */
            'notes' => ['nullable', 'string'],
        ];
    }
}
