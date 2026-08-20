<?php

namespace Domains\CMS\Http\Requests;

use Domains\CMS\Enums\FormSubmissionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cms:submissions:update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(FormSubmissionStatus::class)],
        ];
    }
}
