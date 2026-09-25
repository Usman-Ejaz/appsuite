<?php

namespace Domains\Core\Models;

use Domains\Core\Database\Factories\AppFactory;
use Domains\Core\Enums\AppCode;
use Domains\Core\Enums\AppSiteMode;
use Domains\Identity\Models\Permission;
use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Traits\HasMedia;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class App extends BaseModel
{
    use HasMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'label', 'slug', 'site_mode', 'description', 'is_active', 'category', 'code', 'color', 'icon', 'released_at',
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
            'site_mode' => AppSiteMode::class,
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

    /**
     * The app's branding logo.
     */
    public function logo(): MorphOne
    {
        return $this->singleMediaOfCategory(MediaCategory::LOGO);
    }

    protected static function newFactory(): AppFactory
    {
        return AppFactory::new();
    }
}
