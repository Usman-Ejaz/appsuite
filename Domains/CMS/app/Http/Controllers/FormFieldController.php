<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Actions\ReorderFormFields;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\FormField\CreateRequest;
use Domains\CMS\Http\Requests\FormField\ReorderRequest;
use Domains\CMS\Http\Requests\FormField\UpdateRequest;
use Domains\CMS\Http\Resources\FormFieldCollection;
use Domains\CMS\Http\Resources\FormFieldResource;
use Domains\CMS\Repositories\FormFieldRepository;
use Domains\CMS\Repositories\FormRepository;
use Illuminate\Http\JsonResponse;

class FormFieldController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected FormFieldRepository $fields,
        protected ReorderFormFields $reorderFormFields,
    ) {
        //
    }

    public function list(int $form): FormFieldCollection
    {
        $this->authorize('permission', CmsPermission::ViewForms->value);

        $this->forms->findOrFail($form);

        return new FormFieldCollection($this->fields->filter(['form_id' => $form])->list());
    }

    public function create(CreateRequest $request, int $form): JsonResponse
    {
        $this->forms->findOrFail($form);

        $field = $this->fields->create([...$request->validated(), 'form_id' => $form]);

        return response()->json(['data' => FormFieldResource::make($field)], 201);
    }

    public function reorder(ReorderRequest $request, int $form): JsonResponse
    {
        $formModel = $this->forms->findOrFail($form);

        $this->reorderFormFields->handle($formModel, $request->validated('fields'));

        return response()->json(null, 204);
    }

    public function get(int $form, int $id): FormFieldResource
    {
        $this->authorize('permission', CmsPermission::ViewForms->value);

        $this->forms->findOrFail($form);

        return FormFieldResource::make($this->fields->filter(['form_id' => $form])->findOrFail($id));
    }

    public function update(UpdateRequest $request, int $form, int $id): FormFieldResource
    {
        $this->forms->findOrFail($form);

        $field = $this->fields->filter(['form_id' => $form])->update($id, $request->validated());

        return FormFieldResource::make($field);
    }

    public function delete(int $form, int $id): JsonResponse
    {
        $this->authorize('permission', CmsPermission::UpdateForms->value);

        $this->forms->findOrFail($form);
        $this->fields->filter(['form_id' => $form])->delete($id);

        return response()->json(null, 204);
    }
}
