<?php

namespace Domains\Ecommerce\Actions;

use Domains\Ecommerce\Enums\ReviewStatus;
use Domains\Ecommerce\Models\Review;

class ModerateReview
{
    public function handle(Review $review, ReviewStatus $status): Review
    {
        $review->update(['status' => $status]);

        return $review->fresh();
    }
}
