<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\BaseModel;
use Domains\Identity\Enums\CompanyStatus;
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
}
