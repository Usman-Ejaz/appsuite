<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\App;
use Domains\Core\Models\BaseModel;
use Domains\Identity\Database\Factories\CompanyFactory;
use Domains\Identity\Enums\CompanyStatus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'slug', 'description', 'status', 'license_number',
    ];

    /**
     * The attributes that are able to cast.
     */
    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
        ];
    }

    /**
     * The roles that belong to this company.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * The API keys that belong to this company.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * The apps this company has subscribed to.
     */
    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'company_apps');
    }

    public function hasApp(string $code): bool
    {
        return $this->apps->contains('code', $code);
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
