<?php

namespace Domains\Notification\Models;

use Domains\Notification\Database\Factories\TriggerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A system-owned notification trigger, seeded from a domain's trigger enum, e.g.
 * `Ecommerce:Order:Created`.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $domain
 * @property string $module
 * @property string $app_code
 */
class Trigger extends Model
{
    /** @use HasFactory<TriggerFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['code', 'name', 'description', 'domain', 'module', 'app_code'];

    /**
     * @return HasMany<TriggerAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(TriggerAction::class);
    }

    protected static function newFactory(): TriggerFactory
    {
        return TriggerFactory::new();
    }
}
