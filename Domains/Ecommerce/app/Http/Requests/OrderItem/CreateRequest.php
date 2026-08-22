<?php

namespace Domains\Ecommerce\Http\Requests\OrderItem;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::ORDER_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The product to add to the order.
             *
             * @example 17
             */
            'ecommerce_product_id' => ['required', 'integer', Rule::exists('ecommerce_products', 'id')->where('company_id', $this->user()?->getCompanyId())],

            /**
             * How many units of the product to add. Rejected if this exceeds the product's available stock.
             *
             * @example 2
             */
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
