<?php

namespace Domains\CMS\Enums;

enum BlogStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
