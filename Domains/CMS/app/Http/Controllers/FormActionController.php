<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\CMS\Enums\CMSPermission;
use Domains\CMS\Http\Requests\FormAction\CreateRequest;
use Domains\CMS\Http\Requests\FormAction\UpdateRequest;
use Domains\CMS\Http\Resources\FormActionCollection;
use Domains\CMS\Http\Resources\FormActionResource;
use Domains\CMS\Repositories\FormActionRepository;
use Domains\CMS\Repositories\FormRepository;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Illuminate\Http\JsonResponse;

#[Group('CMS')]
class FormActionController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected FormActionRepository $actions,
    ) {
        //
    }

    /**
     * Get Form Actions
     *
     * Returns the actions belonging to a form.
     */
    public function list(GetCollectionRequest $request, int $form): FormActionCollection
    {
        $this->authorize('permission', CMSPermission::FORM_VIEW);

        $this->forms->findOrFail($form);

        $filters = $request->filters();

        $filters['filter'] = ['AND' => array_values(array_filter([
            ['field' => 'form_id', 'op' => '=', 'value' => $form],
            $filters['filter'] ?? null,
        ]))];

        $records = $this->actions->list($filters);

        return new FormActionCollection($records);
    }

    /**
     * Create Form Action
     *
     * Adds a new action to a form.
     */
    public function create(CreateRequest $request, int $form): JsonResponse
    {
        $this->forms->findOrFail($form);

        $input = $request->validated();

        $action = $this->actions->create([...$input, 'form_id' => $form]);

        return response()->json(['data' => FormActionResource::make($action)], 201);
    }

    /**
     * Get Form Action
     *
     * Returns the details of a single form action.
     */
    public function get(GetResourceRequest $request, int $form, int $id): FormActionResource
    {
        $this->authorize('permission', CMSPermission::FORM_VIEW);

        $this->forms->findOrFail($form);

        $filters = $request->filters();
        $filters['filter'] = ['AND' => array_values(array_filter([
            ['field' => 'form_id', 'op' => '=', 'value' => $form],
            $filters['filter'] ?? null,
        ]))];

        $record = $this->actions->get($id, $filters);

        return FormActionResource::make($record);
    }

    /**
     * Update Form Action
     *
     * Updates an existing form action. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $form, int $id): FormActionResource
    {
        $this->forms->findOrFail($form);

        $input = $request->validated();

        $record = $this->actions->get($id, ['filter' => ['field' => 'form_id', 'op' => '=', 'value' => $form]]);

        $record = $this->actions->update($record, $input);

        return FormActionResource::make($record);
    }

    /**
     * Delete Form Action
     *
     * Permanently removes an action from a form.
     */
    public function delete(int $form, int $id): JsonResponse
    {
        $this->authorize('permission', CMSPermission::FORM_UPDATE);

        $this->forms->findOrFail($form);
        $record = $this->actions->get($id, ['filter' => ['field' => 'form_id', 'op' => '=', 'value' => $form]]);

        $this->actions->delete($record);

        return response()->json(null, 204);
    }
}
