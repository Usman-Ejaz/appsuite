<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\FormAction\CreateRequest;
use Domains\CMS\Http\Requests\FormAction\UpdateRequest;
use Domains\CMS\Http\Resources\FormActionCollection;
use Domains\CMS\Http\Resources\FormActionResource;
use Domains\CMS\Repositories\FormActionRepository;
use Domains\CMS\Repositories\FormRepository;
use Illuminate\Http\JsonResponse;

class FormActionController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected FormActionRepository $actions,
    ) {
        //
    }

    public function list(int $form): FormActionCollection
    {
        $this->authorize('permission', CmsPermission::ViewForms->value);

        $this->forms->findOrFail($form);

        return new FormActionCollection($this->actions->filter(['form_id' => $form])->list());
    }

    public function create(CreateRequest $request, int $form): JsonResponse
    {
        $this->forms->findOrFail($form);

        $action = $this->actions->create([...$request->validated(), 'form_id' => $form]);

        return response()->json(['data' => FormActionResource::make($action)], 201);
    }

    public function get(int $form, int $id): FormActionResource
    {
        $this->authorize('permission', CmsPermission::ViewForms->value);

        $this->forms->findOrFail($form);

        return FormActionResource::make($this->actions->filter(['form_id' => $form])->findOrFail($id));
    }

    public function update(UpdateRequest $request, int $form, int $id): FormActionResource
    {
        $this->forms->findOrFail($form);

        $action = $this->actions->filter(['form_id' => $form])->update($id, $request->validated());

        return FormActionResource::make($action);
    }

    public function delete(int $form, int $id): JsonResponse
    {
        $this->authorize('permission', CmsPermission::UpdateForms->value);

        $this->forms->findOrFail($form);
        $this->actions->filter(['form_id' => $form])->delete($id);

        return response()->json(null, 204);
    }
}
