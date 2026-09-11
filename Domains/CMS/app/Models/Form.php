<?php

namespace Domains\CMS\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Traits\HasSites;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends BaseModel
{
    use HasSites;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'success_message',
        'redirect_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(FormAction::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }
}
