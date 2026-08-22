<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Actions\ModerateReview;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Enums\ReviewStatus;
use Domains\Ecommerce\Http\Requests\Review\CreateRequest;
use Domains\Ecommerce\Http\Requests\Review\ModerateRequest;
use Domains\Ecommerce\Http\Requests\Review\UpdateRequest;
use Domains\Ecommerce\Http\Resources\ReviewCollection;
use Domains\Ecommerce\Http\Resources\ReviewResource;
use Domains\Ecommerce\Repositories\ReviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Reviews')]
class ReviewController extends Controller
{
    public function __construct(
        protected ReviewRepository $reviews,
        protected ModerateReview $moderateReview,
    ) {
        //
    }

    /**
     * Get Reviews
     *
     * Returns a paginated list of reviews for the authenticated company.
     */
    public function list(Request $request): ReviewCollection
    {
        $this->authorize('permission', EcommercePermission::REVIEW_VIEW->value);

        return new ReviewCollection($this->reviews->filter($request->query())->list());
    }

    /**
     * Create Review
     *
     * Submits a new review for a product on behalf of a customer. The review always starts in
     * the `Pending` state, since approving or rejecting it is handled separately through the
     * moderate endpoint.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $review = $this->reviews->create($request->validated());

        return response()->json(['data' => ReviewResource::make($review)], 201);
    }

    /**
     * Get Review
     *
     * Retrieves a single review, including the product it was left on and the customer who left
     * it.
     */
    public function get(int $id): ReviewResource
    {
        $this->authorize('permission', EcommercePermission::REVIEW_VIEW->value);

        return ReviewResource::make($this->reviews->findOrFail($id));
    }

    /**
     * Update Review
     *
     * Updates a review's rating, title, or body.
     */
    public function update(UpdateRequest $request, int $id): ReviewResource
    {
        return ReviewResource::make($this->reviews->update($id, $request->validated()));
    }

    /**
     * Delete Review
     *
     * Permanently removes a review.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::REVIEW_DELETE->value);

        $this->reviews->delete($id);

        return response()->json(null, 204);
    }

    /**
     * Moderate Review
     *
     * Approves or rejects a review that's awaiting moderation. This requires a separate
     * permission from creating or updating a review, since moderation is a distinct privilege.
     */
    public function moderate(ModerateRequest $request, int $id): ReviewResource
    {
        $review = $this->moderateReview->handle(
            $this->reviews->findOrFail($id),
            $request->enum('status', ReviewStatus::class),
        );

        return ReviewResource::make($review);
    }
}
