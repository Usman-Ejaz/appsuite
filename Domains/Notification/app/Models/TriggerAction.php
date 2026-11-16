<?php

namespace Domains\Notification\Models;

use Domains\Core\Models\BaseModel;
use Domains\Notification\Database\Factories\TriggerActionFactory;
use Domains\Notification\Enums\Channel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A company's rule on a trigger: when its conditions pass, it sends its templated subject and
 * body to its recipients through its one channel.
 *
 * @property int $id
 * @property int $company_id
 * @property int $trigger_id
 * @property string $name
 * @property Channel $channel
 * @property string|null $subject
 * @property string|null $body
 * @property array{match?: string, rules?: list<array{field: string, operator: string, value?: mixed}>}|null $conditions
 * @property bool $is_active
 */
class TriggerAction extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'trigger_id',
        'name',
        'channel',
        'subject',
        'body',
        'conditions',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => Channel::class,
            'conditions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Trigger, $this>
     */
    public function trigger(): BelongsTo
    {
        return $this->belongsTo(Trigger::class);
    }

    /**
     * @return HasMany<TriggerActionRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(TriggerActionRecipient::class);
    }

    protected static function newFactory(): TriggerActionFactory
    {
        return TriggerActionFactory::new();
    }
}
