<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Actions\ReorderFormFields;
use Domains\CMS\Http\Requests\CreateFormFieldRequest;
use Domains\CMS\Http\Requests\ReorderFormFieldsRequest;
use Domains\CMS\Http\Requests\UpdateFormFieldRequest;
use Domains\CMS\Http\Resources\FormFieldCollection;
use Domains\CMS\Http\Resources\FormFieldResource;
use Domains\CMS\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormFieldController extends Controller
{
    public function __construct(protected ReorderFormFields $reorderFormFields)
    {
        //
    }

    public function list(Request $request, int $form): FormFieldCollection
    {
        abort_unless($request->user()?->can('cms:forms:view'), 403);

        $formModel = $this->findForm($request, $form);

        return new FormFieldCollection($formModel->fields()->get());
    }

    public function create(CreateFormFieldRequest $request, int $form): JsonResponse
    {
        $formModel = $this->findForm($request, $form);

        $field = $formModel->fields()->create($request->validated());

        return response()->json(['data' => new FormFieldResource($field)], 201);
    }

    public function reorder(ReorderFormFieldsRequest $request, int $form): JsonResponse
    {
        $formModel = $this->findForm($request, $form);

        $this->reorderFormFields->handle($formModel, $request->validated('fields'));

        return response()->json(null, 204);
    }

    public function get(Request $request, int $form, int $id): FormFieldResource
    {
        abort_unless($request->user()?->can('cms:forms:view'), 403);

        $formModel = $this->findForm($request, $form);

        return new FormFieldResource($formModel->fields()->whereKey($id)->firstOrFail());
    }

    public function update(UpdateFormFieldRequest $request, int $form, int $id): FormFieldResource
    {
        $formModel = $this->findForm($request, $form);

        $field = $formModel->fields()->whereKey($id)->firstOrFail();
        $field->update($request->validated());

        return new FormFieldResource($field);
    }

    public function delete(Request $request, int $form, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('cms:forms:update'), 403);

        $formModel = $this->findForm($request, $form);
        $formModel->fields()->whereKey($id)->firstOrFail()->delete();

        return response()->json(null, 204);
    }

    protected function findForm(Request $request, int $form): Form
    {
        return Form::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($form);
    }
}
