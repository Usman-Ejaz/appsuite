<?php

namespace Domains\CMS\Models;

use Domains\CMS\Enums\FormFieldType;
use Domains\Core\Models\BaseModel;
use Domains\Shared\Traits\HasSites;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends BaseModel
{
    use HasSites;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'options',
        'default_value',
        'placeholder',
        'help_text',
        'is_required',
        'is_active',
        'sort_order',
        'validation_rules',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FormFieldType::class,
            'options' => 'array',
            'default_value' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'validation_rules' => 'array',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
