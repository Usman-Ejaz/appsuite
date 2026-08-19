<?php

namespace Domains\Core\Models;

use Domains\Core\Database\Factories\AppFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'label', 'slug', 'description', 'is_active', 'category', 'code', 'color', 'icon', 'released_at',
    ];

    /**
     * The attributes that are able to cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'released_at' => 'datetime',
        ];
    }

    /**
     * The integrations available for this app.
     */
    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    protected static function newFactory(): AppFactory
    {
        return AppFactory::new();
    }
}
