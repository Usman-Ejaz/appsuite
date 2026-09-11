<?php

namespace Domains\CMS\Models;

use Domains\CMS\Enums\FormSubmissionStatus;
use Domains\Core\Models\BaseModel;
use Domains\Shared\Traits\HasSites;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends BaseModel
{
    use HasSites;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'form_id',
        'data',
        'status',
        'ip_address',
        'user_agent',
        'referrer_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'status' => FormSubmissionStatus::class,
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
