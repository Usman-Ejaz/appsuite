<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\CMS\Actions\ReorderFormFields;
use Domains\CMS\Enums\CMSPermission;
use Domains\CMS\Http\Requests\FormField\CreateRequest;
use Domains\CMS\Http\Requests\FormField\ReorderRequest;
use Domains\CMS\Http\Requests\FormField\UpdateRequest;
use Domains\CMS\Http\Resources\FormFieldCollection;
use Domains\CMS\Http\Resources\FormFieldResource;
use Domains\CMS\Repositories\FormFieldRepository;
use Domains\CMS\Repositories\FormRepository;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Illuminate\Http\JsonResponse;

#[Group('CMS')]
class FormFieldController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected FormFieldRepository $fields,
        protected ReorderFormFields $reorderFormFields,
    ) {
        //
    }

    /**
     * Get Form Fields
     *
     * Returns the fields belonging to a form, in their display order.
     */
    public function list(GetCollectionRequest $request, int $form): FormFieldCollection
    {
        $this->authorize('permission', CMSPermission::FORM_VIEW);

        $this->forms->findOrFail($form);

        $filters = $request->filters();
        $filters['filter'] = ['AND' => array_values(array_filter([
            ['field' => 'form_id', 'op' => '=', 'value' => $form],
            $filters['filter'] ?? null,
        ]))];

        $records = $this->fields->list($filters);

        return new FormFieldCollection($records);
    }

    /**
     * Create Form Field
     *
     * Adds a new field to a form.
     */
    public function create(CreateRequest $request, int $form): JsonResponse
    {
        $this->forms->findOrFail($form);

        $input = $request->validated();

        $field = $this->fields->create([...$input, 'form_id' => $form]);

        return response()->json(['data' => FormFieldResource::make($field)], 201);
    }

    /**
     * Reorder Form Fields
     *
     * Sets the display order of a form's fields.
     */
    public function reorder(ReorderRequest $request, int $form): JsonResponse
    {
        $formModel = $this->forms->findOrFail($form);

        $this->reorderFormFields->handle($formModel, $request->validated('fields'));

        return response()->json(null, 204);
    }

    /**
     * Get Form Field
     *
     * Returns the details of a single form field.
     */
    public function get(GetResourceRequest $request, int $form, int $id): FormFieldResource
    {
        $this->authorize('permission', CMSPermission::FORM_VIEW);

        $this->forms->findOrFail($form);

        $filters = $request->filters();
        $filters['filter'] = ['AND' => array_values(array_filter([
            ['field' => 'form_id', 'op' => '=', 'value' => $form],
            $filters['filter'] ?? null,
        ]))];

        $record = $this->fields->get($id, $filters);

        return FormFieldResource::make($record);
    }

    /**
     * Update Form Field
     *
     * Updates an existing form field. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $form, int $id): FormFieldResource
    {
        $this->forms->findOrFail($form);

        $input = $request->validated();

        $record = $this->fields->get($id, ['filter' => ['field' => 'form_id', 'op' => '=', 'value' => $form]]);

        $record = $this->fields->update($record, $input);

        return FormFieldResource::make($record);
    }

    /**
     * Delete Form Field
     *
     * Permanently removes a field from a form.
     */
    public function delete(int $form, int $id): JsonResponse
    {
        $this->authorize('permission', CMSPermission::FORM_UPDATE);

        $this->forms->findOrFail($form);
        $record = $this->fields->get($id, ['filter' => ['field' => 'form_id', 'op' => '=', 'value' => $form]]);

        $this->fields->delete($record);

        return response()->json(null, 204);
    }
}
