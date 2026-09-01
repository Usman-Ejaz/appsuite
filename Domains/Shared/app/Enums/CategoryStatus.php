<?php

namespace Domains\Shared\Enums;

/**
 * The category's stage in its publishing lifecycle.
 */
enum CategoryStatus: string
{
    /**
     * The category has been created but is not yet visible.
     */
    case DRAFT = 'Draft';

    /**
     * The category is visible and usable.
     */
    case ACTIVE = 'Active';

    /**
     * The category is no longer visible or usable.
     */
    case ARCHIVED = 'Archived';
}
