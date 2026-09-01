<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\CMS\Actions\SubmitForm;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\Form\CreateRequest;
use Domains\CMS\Http\Requests\Form\SubmitRequest;
use Domains\CMS\Http\Requests\Form\UpdateRequest;
use Domains\CMS\Http\Resources\FormCollection;
use Domains\CMS\Http\Resources\FormResource;
use Domains\CMS\Http\Resources\FormSubmissionResource;
use Domains\CMS\Repositories\FormRepository;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Illuminate\Http\JsonResponse;

#[Group('CMS')]
class FormController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected SubmitForm $submitForm,
    ) {
        //
    }

    /**
     * Get Forms
     *
     * Returns the forms that belong to the company.
     */
    public function list(GetCollectionRequest $request): FormCollection
    {
        $this->authorize('permission', CmsPermission::FORM_VIEW->value);

        $filters = $request->filters();

        $records = $this->forms->list($filters);

        return new FormCollection($records);
    }

    /**
     * Create Form
     *
     * Creates a new form for the company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $form = $this->forms->create($input);

        return response()->json(['data' => FormResource::make($form)], 201);
    }

    /**
     * Get Form
     *
     * Returns the details of a single form.
     */
    public function get(GetResourceRequest $request, int $form): FormResource
    {
        $this->authorize('permission', CmsPermission::FORM_VIEW->value);

        $filters = $request->filters();

        $record = $this->forms->get($form, $filters);

        return FormResource::make($record);
    }

    /**
     * Update Form
     *
     * Updates an existing form. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $form): FormResource
    {
        $input = $request->validated();

        $record = $this->forms->update($form, $input);

        return FormResource::make($record);
    }

    /**
     * Delete Form
     *
     * Permanently removes a form.
     */
    public function delete(int $form): JsonResponse
    {
        $this->authorize('permission', CmsPermission::FORM_DELETE->value);

        $this->forms->delete($form);

        return response()->json(null, 204);
    }

    /**
     * Submit Form
     *
     * Submits a response to a form on behalf of a visitor.
     */
    public function submit(SubmitRequest $request, int $form): JsonResponse
    {
        $formModel = $this->forms->findOrFail($form);

        $submission = $this->submitForm->handle(
            $formModel,
            $request->validated('data'),
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json(['data' => FormSubmissionResource::make($submission)], 201);
    }
}
