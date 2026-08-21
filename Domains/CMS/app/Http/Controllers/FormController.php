<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Actions\SubmitForm;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\Form\CreateRequest;
use Domains\CMS\Http\Requests\Form\SubmitRequest;
use Domains\CMS\Http\Requests\Form\UpdateRequest;
use Domains\CMS\Http\Resources\FormCollection;
use Domains\CMS\Http\Resources\FormResource;
use Domains\CMS\Http\Resources\FormSubmissionResource;
use Domains\CMS\Repositories\FormRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected SubmitForm $submitForm,
    ) {
        //
    }

    public function list(Request $request): FormCollection
    {
        $this->authorize('permission', CmsPermission::ViewForms->value);

        return new FormCollection($this->forms->filter($request->query())->list());
    }

    public function create(CreateRequest $request): JsonResponse
    {
        $form = $this->forms->create($request->validated());

        return response()->json(['data' => FormResource::make($form)], 201);
    }

    public function get(int $form): FormResource
    {
        $this->authorize('permission', CmsPermission::ViewForms->value);

        return FormResource::make($this->forms->findOrFail($form));
    }

    public function update(UpdateRequest $request, int $form): FormResource
    {
        return FormResource::make($this->forms->update($form, $request->validated()));
    }

    public function delete(int $form): JsonResponse
    {
        $this->authorize('permission', CmsPermission::DeleteForms->value);

        $this->forms->delete($form);

        return response()->json(null, 204);
    }

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
