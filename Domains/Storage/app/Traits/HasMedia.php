<?php

namespace Domains\Storage\Traits;

use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasMedia
{
    /**
     * All media items attached to this resource.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'resource');
    }

    /**
     * The MediaCategory values this resource accepts media under. Override to restrict.
     *
     * @return array<int, MediaCategory>
     */
    public function allowedMediaCategories(): array
    {
        return MediaCategory::cases();
    }

    /**
     * A category-filtered relation for a resource that can hold several media items
     * under the same category (e.g. a product's gallery images).
     */
    protected function mediaOfCategory(MediaCategory $category): MorphMany
    {
        return $this->media()->where('category', $category->value);
    }

    /**
     * A category-filtered relation for a resource that holds a single media item per
     * category (e.g. a user's avatar). Resolves to the most recently uploaded one.
     */
    protected function singleMediaOfCategory(MediaCategory $category): MorphOne
    {
        return $this->morphOne(Media::class, 'resource')
            ->where('category', $category->value)
            ->latest('id');
    }
}
