<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\FormSubmission\UpdateRequest;
use Domains\CMS\Http\Resources\FormSubmissionCollection;
use Domains\CMS\Http\Resources\FormSubmissionResource;
use Domains\CMS\Repositories\FormRepository;
use Domains\CMS\Repositories\FormSubmissionRepository;
use Domains\Identity\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function __construct(
        protected FormRepository $forms,
        protected FormSubmissionRepository $submissions,
    ) {
        //
    }

    public function list(Request $request, int $form): FormSubmissionCollection
    {
        abort_if($request->user() instanceof ApiKey, 403);
        $this->authorize('permission', CmsPermission::ViewSubmissions->value);

        $this->forms->findOrFail($form);

        return new FormSubmissionCollection($this->submissions->filter(['form_id' => $form])->list());
    }

    public function get(Request $request, int $form, int $id): FormSubmissionResource
    {
        abort_if($request->user() instanceof ApiKey, 403);
        $this->authorize('permission', CmsPermission::ViewSubmissions->value);

        $this->forms->findOrFail($form);

        return FormSubmissionResource::make($this->submissions->filter(['form_id' => $form])->findOrFail($id));
    }

    public function update(UpdateRequest $request, int $form, int $id): FormSubmissionResource
    {
        $this->forms->findOrFail($form);

        $submission = $this->submissions->filter(['form_id' => $form])->update($id, $request->validated());

        return FormSubmissionResource::make($submission);
    }

    public function delete(Request $request, int $form, int $id): JsonResponse
    {
        abort_if($request->user() instanceof ApiKey, 403);
        $this->authorize('permission', CmsPermission::DeleteSubmissions->value);

        $this->forms->findOrFail($form);
        $this->submissions->filter(['form_id' => $form])->delete($id);

        return response()->json(null, 204);
    }
}
