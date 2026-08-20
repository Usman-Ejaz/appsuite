<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Http\Requests\UpdateFormSubmissionRequest;
use Domains\CMS\Http\Resources\FormSubmissionCollection;
use Domains\CMS\Http\Resources\FormSubmissionResource;
use Domains\CMS\Models\Form;
use Domains\Identity\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function list(Request $request, int $form): FormSubmissionCollection
    {
        abort_if($request->user() instanceof ApiKey, 403);
        abort_unless($request->user()?->can('cms:submissions:view'), 403);

        $formModel = $this->findForm($request, $form);

        return new FormSubmissionCollection($formModel->submissions()->latest()->paginate());
    }

    public function get(Request $request, int $form, int $id): FormSubmissionResource
    {
        abort_if($request->user() instanceof ApiKey, 403);
        abort_unless($request->user()?->can('cms:submissions:view'), 403);

        $formModel = $this->findForm($request, $form);

        return new FormSubmissionResource($formModel->submissions()->whereKey($id)->firstOrFail());
    }

    public function update(UpdateFormSubmissionRequest $request, int $form, int $id): FormSubmissionResource
    {
        abort_if($request->user() instanceof ApiKey, 403);

        $formModel = $this->findForm($request, $form);

        $submission = $formModel->submissions()->whereKey($id)->firstOrFail();
        $submission->update($request->validated());

        return new FormSubmissionResource($submission);
    }

    public function delete(Request $request, int $form, int $id): JsonResponse
    {
        abort_if($request->user() instanceof ApiKey, 403);
        abort_unless($request->user()?->can('cms:submissions:delete'), 403);

        $formModel = $this->findForm($request, $form);
        $formModel->submissions()->whereKey($id)->firstOrFail()->delete();

        return response()->json(null, 204);
    }

    protected function findForm(Request $request, int $form): Form
    {
        return Form::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($form);
    }
}
