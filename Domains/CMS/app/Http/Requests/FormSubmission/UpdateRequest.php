<?php

namespace Domains\CMS\Http\Requests\FormSubmission;

use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Enums\FormSubmissionStatus;
use Domains\Identity\Models\ApiKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * ApiKey actors (public-website integrations) can never update
     * submissions, regardless of their granted abilities — a hard-coded
     * restriction, not a permission string.
     */
    public function authorize(): bool
    {
        return ! ($this->user() instanceof ApiKey) && Gate::allows('permission', CmsPermission::UpdateSubmissions->value);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(FormSubmissionStatus::class)],
        ];
    }
}
