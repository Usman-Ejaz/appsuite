<?php

namespace Domains\Ecommerce\Http\Requests\Order;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::ORDER_APPLY_COUPON);
    }

    public function rules(): array
    {
        return [
            /**
             * The coupon code to apply to the order.
             *
             * @example SAVE10
             */
            'code' => ['required', 'string', 'max:50'],
        ];
    }
}
