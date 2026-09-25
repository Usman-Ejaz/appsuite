<?php

namespace Domains\Core\Models;

use Domains\Core\Database\Factories\IntegrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'app_id', 'name', 'provider', 'config', 'is_active',
    ];

    /**
     * The attributes that are able to cast.
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The app this integration belongs to.
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    protected static function newFactory(): IntegrationFactory
    {
        return IntegrationFactory::new();
    }
}
