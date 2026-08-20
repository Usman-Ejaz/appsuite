<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Http\Requests\CreateFormActionRequest;
use Domains\CMS\Http\Requests\UpdateFormActionRequest;
use Domains\CMS\Http\Resources\FormActionCollection;
use Domains\CMS\Http\Resources\FormActionResource;
use Domains\CMS\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormActionController extends Controller
{
    public function list(Request $request, int $form): FormActionCollection
    {
        abort_unless($request->user()?->can('cms:forms:view'), 403);

        $formModel = $this->findForm($request, $form);

        return new FormActionCollection($formModel->actions()->get());
    }

    public function create(CreateFormActionRequest $request, int $form): JsonResponse
    {
        $formModel = $this->findForm($request, $form);

        $action = $formModel->actions()->create($request->validated());

        return response()->json(['data' => new FormActionResource($action)], 201);
    }

    public function get(Request $request, int $form, int $id): FormActionResource
    {
        abort_unless($request->user()?->can('cms:forms:view'), 403);

        $formModel = $this->findForm($request, $form);

        return new FormActionResource($formModel->actions()->whereKey($id)->firstOrFail());
    }

    public function update(UpdateFormActionRequest $request, int $form, int $id): FormActionResource
    {
        $formModel = $this->findForm($request, $form);

        $action = $formModel->actions()->whereKey($id)->firstOrFail();
        $action->update($request->validated());

        return new FormActionResource($action);
    }

    public function delete(Request $request, int $form, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('cms:forms:update'), 403);

        $formModel = $this->findForm($request, $form);
        $formModel->actions()->whereKey($id)->firstOrFail()->delete();

        return response()->json(null, 204);
    }

    protected function findForm(Request $request, int $form): Form
    {
        return Form::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($form);
    }
}
