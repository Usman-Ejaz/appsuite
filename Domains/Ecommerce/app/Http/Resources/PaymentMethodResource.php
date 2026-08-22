<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Shared\Models\PaymentMethod;
use Illuminate\Http\Request;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.payment-methods';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,

            /**
             * The name shown for this payment method at checkout.
             */
            'name' => $this->name,

            /**
             * The kind of payment method this is.
             */
            'type' => $this->type,

            /**
             * The processor or bank behind this payment method. Only meaningful for some
             * types.
             */
            'provider' => $this->provider,

            /**
             * Freeform text shown to the customer at checkout, such as bank account details
             * for a `Bank Transfer` method.
             */
            'instructions' => $this->instructions,

            /**
             * Whether this is the company's preferred payment method at checkout.
             */
            'is_default' => $this->is_default,

            /**
             * Whether this payment method can currently be offered at checkout.
             */
            'is_active' => $this->is_active,
        ]);
    }
}
