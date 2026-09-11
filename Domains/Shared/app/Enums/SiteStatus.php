<?php

namespace Domains\Shared\Enums;

/**
 * The site's stage in its publishing lifecycle.
 */
enum SiteStatus: string
{
    /**
     * The site has been created but is not yet visible.
     */
    case DRAFT = 'Draft';

    /**
     * The site is visible and usable.
     */
    case ACTIVE = 'Active';

    /**
     * The site is no longer visible or usable.
     */
    case ARCHIVED = 'Archived';
}
