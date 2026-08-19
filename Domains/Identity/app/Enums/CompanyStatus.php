<?php

namespace Domains\Identity\Enums;

enum CompanyStatus: string
{
    case ACTIVE = 'Active';
    case IN_ACTIVE = 'In active';
    case BLOCKED = 'Blocked';
}
