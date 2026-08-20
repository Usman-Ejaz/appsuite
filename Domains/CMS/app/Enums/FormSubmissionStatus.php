<?php

namespace Domains\CMS\Enums;

enum FormSubmissionStatus: string
{
    case NEW = 'new';
    case READ = 'read';
    case ARCHIVED = 'archived';
}
