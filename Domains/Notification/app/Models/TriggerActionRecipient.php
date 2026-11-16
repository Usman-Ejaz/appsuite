<?php

namespace Domains\Notification\Models;

use Domains\Notification\Database\Factories\TriggerActionRecipientFactory;
use Domains\Notification\Enums\RecipientType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Who a trigger action notifies. The column used depends on the type: `recipient` for a user
 * or role, `code` for a dynamic recipient, `address` for a fixed address.
 *
 * @property int $id
 * @property int $trigger_action_id
 * @property RecipientType $type
 * @property string|null $recipient_type
 * @property int|null $recipient_id
 * @property string|null $code
 * @property string|null $address
 */
class TriggerActionRecipient extends Model
{
    /** @use HasFactory<TriggerActionRecipientFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['trigger_action_id', 'type', 'recipient_type', 'recipient_id', 'code', 'address'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RecipientType::class,
        ];
    }

    /**
     * @return BelongsTo<TriggerAction, $this>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(TriggerAction::class, 'trigger_action_id');
    }

    /**
     * The user or role, for the `User` and `Role` types.
     *
     * @return MorphTo<Model, $this>
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): TriggerActionRecipientFactory
    {
        return TriggerActionRecipientFactory::new();
    }
}
