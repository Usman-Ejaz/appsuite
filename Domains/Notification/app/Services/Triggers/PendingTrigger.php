<?php

namespace Domains\Notification\Services\Triggers;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * A trigger being configured by `TriggerDispatcher::dispatch()`. Like Laravel's `PendingDispatch`,
 * it queues its trigger action when it goes out of scope, so the chain needs no closing call.
 */
class PendingTrigger
{
    protected ?Model $resource = null;

    /**
     * @var array<string, mixed>
     */
    protected array $meta = [];

    protected ?string $queue = null;

    public function __construct(protected BackedEnum $trigger)
    {
        //
    }

    /**
     * The record the trigger is about, e.g. the order that was placed. Required.
     */
    public function for(Model $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Extra data for the trigger action, e.g. `['previous' => ['order.status' => 'Pending']]`.
     *
     * @param  array<string, mixed>  $meta
     */
    public function with(array $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * The queue the trigger and the notifications it sends run on, instead of `notification.queue`.
     */
    public function onQueue(string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Queues the trigger action for the record.
     */
    public function __destruct()
    {
        if (! $this->resource) {
            throw new LogicException("Trigger [{$this->trigger->value}] was dispatched without a record; chain ->for(\$model).");
        }

        $pending = TriggerActionResolver::make($this->resource)::dispatch($this->resource, $this->trigger, $this->meta);

        if ($this->queue) {
            $pending->onQueue($this->queue);
        }
    }
}
