<?php

namespace Domains\CMS\Enums;

/**
 * The blog post's stage in its publishing lifecycle.
 */
enum BlogStatus: string
{
    /**
     * The blog post has been created but is not publicly visible.
     */
    case DRAFT = 'Draft';

    /**
     * The blog post is publicly visible.
     */
    case PUBLISHED = 'Published';

    /**
     * The blog post is no longer publicly visible.
     */
    case ARCHIVED = 'Archived';
}
