<?php

namespace Domains\Ecommerce\Http\Requests\OrderItem;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::ORDER_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The new quantity for this line item. Stock is adjusted by the difference between the old and new quantity, and is rejected if the increase exceeds available stock.
             *
             * @example 3
             */
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
