<?php

namespace Domains\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasParentChild
{
    protected string $parent_column = 'parent_id';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Children recursively.
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Get all ancestors from immediate parent to root.
     */
    public function ancestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;

        while ($parent) {
            $ancestors[] = $parent;
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendants recursively.
     */
    public function descendants(): array
    {
        $descendants = [];

        foreach ($this->children as $child) {
            $descendants[] = $child;
            $descendants = array_merge(
                $descendants,
                $child->descendants()
            );
        }

        return $descendants;
    }

    /**
     * Get root/top-level parent.
     */
    public function root(): Model
    {
        $model = $this;

        while ($model->parent) {
            $model = $model->parent;
        }

        return $model;
    }

    public function isRoot(): bool
    {
        return is_null($this->{$this->parent_column});
    }

    public function isLeaf(): bool
    {
        return ! $this->hasChildren();
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Number of levels from root.
     */
    public function depth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Scope only root records.
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope only child records.
     */
    public function scopeChildren(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }
}
