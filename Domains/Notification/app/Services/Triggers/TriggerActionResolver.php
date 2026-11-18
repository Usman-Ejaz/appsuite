<?php

namespace Domains\Notification\Services\Triggers;

use Domains\CMS\Models\FormSubmission;
use Domains\Ecommerce\Models\Order;
use Domains\Notification\Actions\AbstractTriggerAction;
use Domains\Notification\Actions\TriggerFormSubmissionAction;
use Domains\Notification\Actions\TriggerOrderAction;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TriggerActionResolver
{
    /**
     * The only record-based dispatch. Supporting a new kind of record means one new trigger
     * action plus one new arm here.
     *
     * @return class-string<AbstractTriggerAction>
     */
    public static function make(Model $resource): string
    {
        return match (true) {
            $resource instanceof Order => TriggerOrderAction::class,
            $resource instanceof FormSubmission => TriggerFormSubmissionAction::class,
            default => throw new InvalidArgumentException('No trigger action handles ['.$resource::class.'] records.'),
        };
    }
}
