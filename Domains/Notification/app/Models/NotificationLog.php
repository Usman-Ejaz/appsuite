<?php

namespace Domains\Notification\Models;

use Domains\Core\Traits\HasCompany;
use Domains\Notification\Database\Factories\NotificationLogFactory;
use Domains\Notification\Enums\Channel;
use Domains\Notification\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One trigger notification that was sent, or failed, to one recipient.
 *
 * @property int $id
 * @property int $company_id
 * @property int|null $trigger_id
 * @property int|null $trigger_action_id
 * @property Channel $channel
 * @property string|null $notifiable_type
 * @property int|null $notifiable_id
 * @property string|null $address
 * @property string|null $resource_type
 * @property int|null $resource_id
 * @property array{subject?: string, body?: string}|null $data
 * @property NotificationStatus $status
 * @property string|null $error
 */
class NotificationLog extends Model
{
    use HasCompany;

    /** @use HasFactory<NotificationLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'trigger_id',
        'trigger_action_id',
        'channel',
        'notifiable_type',
        'notifiable_id',
        'address',
        'resource_type',
        'resource_id',
        'data',
        'status',
        'error',
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
            'data' => 'array',
            'status' => NotificationStatus::class,
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
     * @return BelongsTo<TriggerAction, $this>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(TriggerAction::class, 'trigger_action_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function resource(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): NotificationLogFactory
    {
        return NotificationLogFactory::new();
    }
}
