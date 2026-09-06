<?php

namespace Domains\Identity\Enums;

/**
 * The standing of a company account.
 */
enum CompanyStatus: string
{
    /**
     * The company is in good standing and can use the platform normally.
     */
    case ACTIVE = 'Active';

    /**
     * The company is not currently active and its access is suspended.
     */
    case IN_ACTIVE = 'In Active';

    /**
     * The company has been blocked and cannot access the platform.
     */
    case BLOCKED = 'Blocked';
}
