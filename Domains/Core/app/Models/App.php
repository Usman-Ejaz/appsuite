<?php

namespace Domains\Core\Models;

use Domains\Core\Database\Factories\AppFactory;
use Domains\Core\Enums\AppCode;
use Domains\Identity\Models\Permission;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends BaseModel
{
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
            'code' => AppCode::class,
        ];
    }

    #[Scope]
    public function withoutSystem(Builder $query)
    {
        $query->where('code', '<>', AppCode::SYSTEM);
    }

    #[Scope]
    public function system(Builder $query)
    {
        $query->where('code', AppCode::SYSTEM);
    }

    /**
     * The integrations available for this app.
     */
    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    /**
     * The permissions available for this app.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    protected static function newFactory(): AppFactory
    {
        return AppFactory::new();
    }
}
