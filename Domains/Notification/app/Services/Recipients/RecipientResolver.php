<?php

namespace Domains\Notification\Services\Recipients;

use Domains\Notification\Enums\RecipientType;
use Domains\Notification\Services\Recipients\Contracts\TriggerRecipient;

class RecipientResolver
{
    /**
     * The only recipient-type dispatch. Adding a recipient type means one new class plus one new
     * arm here.
     */
    public static function make(RecipientType $type): TriggerRecipient
    {
        return match ($type) {
            RecipientType::USER => new UserRecipient,
            RecipientType::ROLE => new RoleRecipient,
            RecipientType::OWNER => new OwnerRecipient,
            RecipientType::DYNAMIC => new DynamicRecipient,
            RecipientType::STATIC => new StaticRecipient,
        };
    }
}
