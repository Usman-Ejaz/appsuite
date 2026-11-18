<?php

namespace Domains\Notification\Services\Triggers;

use BackedEnum;

/**
 * The one way to fire a trigger. Call it from the repository method or action that performs the
 * business event, never from a model hook or observer:
 *
 *     TriggerDispatcher::dispatch(CMSTrigger::FORM_SUBMITTED)->for($submission);
 *     TriggerDispatcher::dispatch(EcommerceTrigger::ORDER_STATUS_CHANGED)
 *         ->for($order)
 *         ->with(['previous' => ['order.status' => 'Pending']])
 *         ->onQueue('ecommerce');
 *
 * The trigger is queued once the chain ends. Without `onQueue()` it runs on `notification.queue`.
 */
class TriggerDispatcher
{
    public static function dispatch(BackedEnum $trigger): PendingTrigger
    {
        return new PendingTrigger($trigger);
    }
}
