<?php

namespace Domains\Shared\Enums;

/**
 * The product's stage in its selling lifecycle.
 */
enum ProductStatus: string
{
    /**
     * The product has been created but is not yet available for sale.
     */
    case DRAFT = 'Draft';

    /**
     * The product is available for sale.
     */
    case ACTIVE = 'Active';

    /**
     * The product is no longer available for sale.
     */
    case ARCHIVED = 'Archived';
}
