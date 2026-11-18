<?php

namespace Domains\Notification\Actions;

use BackedEnum;
use Domains\Identity\Models\Company;
use Domains\Notification\Models\Trigger;
use Domains\Notification\Models\TriggerAction;
use Domains\Notification\Models\TriggerActionRecipient;
use Domains\Notification\Notifications\TriggerNotification;
use Domains\Notification\Repositories\TriggerActionRepository;
use Domains\Notification\Services\Channels\ChannelResolver;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Domains\Notification\Services\ConditionEvaluator;
use Domains\Notification\Services\Recipients\RecipientResolver;
use Domains\Notification\Services\Tokens\AbstractTokenResolver;
use Domains\Notification\Services\Tokens\ActorTokenResolver;
use Domains\Notification\Services\Tokens\CompanyTokenResolver;
use Domains\Notification\Services\Tokens\RecipientTokenResolver;
use Domains\Notification\Services\Tokens\TokenParser;
use Domains\Notification\Services\Tokens\TriggerTokenResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Runs a company's trigger actions for one record. Queued after the surrounding transaction
 * commits, so nothing here slows the request or fires for a rolled-back write.
 *
 * Each subclass covers one kind of record (an order, a form submission) and only says what
 * differs: which token resolvers describe it, and who its dynamic recipients are.
 */
abstract class AbstractTriggerAction implements ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $meta  Extra data, e.g. `['previous' => ['order.status' => 'Pending']]`
     *                                      holding the values fields had before a change, keyed by token.
     */
    public function __construct(
        public Model $resource,
        public BackedEnum $trigger,
        public array $meta = [],
        public ?Model $actor = null,
    ) {
        $this->actor ??= Auth::user();
        $this->onQueue(config('notification.queue'));
    }

    /**
     * The token resolvers describing the record, e.g. an order and its customer.
     *
     * @return list<AbstractTokenResolver>
     */
    abstract protected function tokenResolvers(): array;

    /**
     * The people a dynamic recipient code means for this record, e.g. `customer`.
     *
     * @return Collection<int, object>
     */
    public function dynamicRecipients(string $code): Collection
    {
        return collect();
    }

    public function handle(TriggerActionRepository $repository, TokenParser $parser, ConditionEvaluator $conditions): void
    {
        $companyId = $this->resource->getAttribute('company_id');
        $trigger = Trigger::where('code', $this->trigger->value)->first();

        if (! $companyId || ! $trigger) {
            return;
        }

        $actions = $repository->activeFor($companyId, $trigger->id);

        if ($actions->isEmpty()) {
            return;
        }

        $resolvers = TokenParser::keyed([
            ...$this->tokenResolvers(),
            new CompanyTokenResolver(Company::whereKey($companyId)->first()),
            new ActorTokenResolver($this->actor),
            new TriggerTokenResolver($trigger),
        ]);

        foreach ($actions as $action) {
            $channel = ChannelResolver::make($action->channel);
            $value = fn (string $token) => $parser->value($token, $resolvers);

            if (! $channel->driver() || ! $conditions->passes($action->conditions, $value, $this->meta['previous'] ?? [])) {
                continue;
            }

            foreach ($this->recipients($action, $channel, $companyId) as $notifiable) {
                $tokens = [...$resolvers, 'recipient' => new RecipientTokenResolver($notifiable)];

                $notification = new TriggerNotification(
                    $action,
                    $this->resource,
                    $parser->parse($action->subject, $tokens),
                    $parser->parse($action->body, $tokens, html: $channel->isHtml()),
                );

                Notification::send($notifiable, $notification->onQueue($this->queue));
            }
        }
    }

    /**
     * Everyone the action notifies that its channel can reach, each once.
     *
     * @return Collection<int, object>
     */
    protected function recipients(TriggerAction $action, NotificationChannel $channel, int $companyId): Collection
    {
        return $action->recipients
            ->flatMap(fn (TriggerActionRecipient $recipient) => RecipientResolver::make($recipient->type)
                ->resolve($recipient, $this, $channel, $companyId))
            ->filter(fn (object $notifiable) => $channel->canReach($notifiable))
            ->unique(fn (object $notifiable) => $notifiable instanceof AnonymousNotifiable
                ? json_encode($notifiable->routes)
                : ($notifiable instanceof Model ? $notifiable->getMorphClass().':'.$notifiable->getKey() : spl_object_id($notifiable)))
            ->values();
    }
}
