<?php

namespace Domains\Core\Http\Requests;

use Illuminate\Support\Arr;
use Kalimulhaq\Qubuilder\Rules\ValidateJson;
use Kalimulhaq\Qubuilder\Support\Helper;

/**
 * App-wide base for single-record endpoints. Every domain's own GetResourceRequest
 * should extend this rather than the package class directly.
 *
 * Extends this app's GetCollectionRequest (not the package's) purely to inherit its
 * loosened authorize()/ValidateJson rule-building — passedValidation() below still
 * narrows the stored filters to select/include only, same as the package base.
 */
class GetResourceRequest extends GetCollectionRequest
{
    public function rules(): array
    {
        return [
            Helper::param('select') => ['sometimes', new ValidateJson],
            Helper::param('include') => ['sometimes', new ValidateJson],
        ];
    }

    protected function passedValidation(): void
    {
        $this->filters = Arr::only(Helper::input($this), ['select', 'include']);
    }
}
