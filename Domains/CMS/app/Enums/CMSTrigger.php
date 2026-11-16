<?php

namespace Domains\CMS\Enums;

/**
 * The CMS events companies can attach notification actions to.
 */
enum CMSTrigger: string
{
    /**
     * Someone submitted one of the company's forms.
     */
    case FORM_SUBMITTED = 'CMS:Form:Submitted';
}
