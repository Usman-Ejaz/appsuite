<?php

namespace Domains\CMS\Enums;

enum BlogStatus: string
{
    case DRAFT = 'Draft';
    case PUBLISHED = 'Published';
    case ARCHIVED = 'Archived';
}
