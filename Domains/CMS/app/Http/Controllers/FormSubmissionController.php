<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\CMS\Enums\CMSPermission;
use Domains\CMS\Http\Requests\FormSubmission\UpdateRequest;
use Domains\CMS\Http\Resources\FormSubmissionCollection;
use Domains\CMS\Http\Resources\FormSubmissionResource;
use Domains\CMS\Repositories\FormRepository;
use Domains\CMS\Repositories\FormSubmissionRepository;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Identity\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('CMS')]
class FormSubmissionController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected FormSubmissionRepository $submissions,
    ) {
        //
    }

    /**
     * Get Form Submissions
     *
     * Returns the submissions belonging to a form.
     */
    public function list(GetCollectionRequest $request, int $form): FormSubmissionCollection
    {
        abort_if($request->user() instanceof ApiKey, 403);

        $this->authorize('permission', CMSPermission::FORM_SUBMISSIONS_VIEW);

        $this->forms->findOrFail($form);

        $filters = $request->filters();
        $filters['filter'] = ['AND' => array_values(array_filter([
            ['field' => 'form_id', 'op' => '=', 'value' => $form],
            $filters['filter'] ?? null,
        ]))];

        $records = $this->submissions->list($filters);

        return new FormSubmissionCollection($records);
    }

    /**
     * Get Form Submission
     *
     * Returns the details of a single form submission.
     */
    public function get(GetResourceRequest $request, int $form, int $id): JsonResponse
    {
        abort_if($request->user() instanceof ApiKey, 403);
        $this->authorize('permission', CMSPermission::FORM_SUBMISSIONS_VIEW);

        $this->forms->findOrFail($form);

        $filters = $request->filters();
        $filters['filter'] = ['AND' => array_values(array_filter([
            ['field' => 'form_id', 'op' => '=', 'value' => $form],
            $filters['filter'] ?? null,
        ]))];

        $record = $this->submissions->get($id, $filters);

        // Wrapped by hand: the resource has its own `data` field, so Laravel would not wrap it.
        return response()->json(['data' => FormSubmissionResource::make($record)]);
    }

    /**
     * Update Form Submission
     *
     * Updates an existing form submission. Fields left out of the request keep their current
     * value.
     */
    public function update(UpdateRequest $request, int $form, int $id): JsonResponse
    {
        $this->forms->findOrFail($form);

        $input = $request->validated();

        $record = $this->submissions->get($id, ['filter' => ['field' => 'form_id', 'op' => '=', 'value' => $form]]);

        $record = $this->submissions->update($record, $input);

        // Wrapped by hand: the resource has its own `data` field, so Laravel would not wrap it.
        return response()->json(['data' => FormSubmissionResource::make($record)]);
    }

    /**
     * Delete Form Submission
     *
     * Permanently removes a submission from a form.
     */
    public function delete(Request $request, int $form, int $id): JsonResponse
    {
        abort_if($request->user() instanceof ApiKey, 403);
        $this->authorize('permission', CMSPermission::FORM_SUBMISSIONS_DELETE);

        $this->forms->findOrFail($form);
        $record = $this->submissions->get($id, ['filter' => ['field' => 'form_id', 'op' => '=', 'value' => $form]]);

        $this->submissions->delete($record);

        return response()->json(null, 204);
    }
}
