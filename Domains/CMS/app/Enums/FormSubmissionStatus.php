<?php

namespace Domains\CMS\Enums;

enum FormSubmissionStatus: string
{
    case NEW = 'New';
    case READ = 'Read';
    case SPAM = 'Spam';
}
