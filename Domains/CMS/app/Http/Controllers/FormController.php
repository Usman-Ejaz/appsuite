<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Actions\SubmitForm;
use Domains\CMS\Http\Requests\CreateFormRequest;
use Domains\CMS\Http\Requests\SubmitFormRequest;
use Domains\CMS\Http\Requests\UpdateFormRequest;
use Domains\CMS\Http\Resources\FormCollection;
use Domains\CMS\Http\Resources\FormResource;
use Domains\CMS\Http\Resources\FormSubmissionResource;
use Domains\CMS\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function __construct(protected SubmitForm $submitForm)
    {
        //
    }

    public function list(Request $request): FormCollection
    {
        abort_unless($request->user()?->can('cms:forms:view'), 403);

        $forms = Form::query()
            ->where('company_id', $request->user()->getCompanyId())
            ->latest()
            ->paginate();

        return new FormCollection($forms);
    }

    public function create(CreateFormRequest $request): JsonResponse
    {
        $form = Form::create($request->validated());

        return response()->json(['data' => new FormResource($form)], 201);
    }

    public function get(Request $request, int $form): FormResource
    {
        abort_unless($request->user()?->can('cms:forms:view'), 403);

        $formModel = Form::query()
            ->where('company_id', $request->user()->getCompanyId())
            ->with(['fields' => fn ($query) => $query->orderBy('sort_order')])
            ->findOrFail($form);

        return new FormResource($formModel);
    }

    public function update(UpdateFormRequest $request, int $form): FormResource
    {
        $formModel = Form::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($form);

        $formModel->update($request->validated());

        return new FormResource($formModel);
    }

    public function delete(Request $request, int $form): JsonResponse
    {
        abort_unless($request->user()?->can('cms:forms:delete'), 403);

        Form::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($form)->delete();

        return response()->json(null, 204);
    }

    public function submit(SubmitFormRequest $request, int $form): JsonResponse
    {
        $formModel = Form::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($form);

        $submission = $this->submitForm->handle(
            $formModel,
            $request->validated('data'),
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json(['data' => new FormSubmissionResource($submission)], 201);
    }
}
