<?php

namespace Domains\CMS\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Traits\HasSites;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAction extends BaseModel
{
    use HasSites;

    /**
     * The attributes that are mass assignable.
     *
     * `type`/`config` are not resolved or executed against anything yet —
     * see the TODO in Domains/CMS/app/Actions/SubmitForm.php.
     */
    protected $fillable = [
        'form_id',
        'type',
        'name',
        'config',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
