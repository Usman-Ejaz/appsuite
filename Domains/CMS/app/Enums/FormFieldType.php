<?php

namespace Domains\CMS\Enums;

enum FormFieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case NUMBER = 'number';
    case URL = 'url';
    case DATE = 'date';
    case SELECT = 'select';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case CHECKBOX_GROUP = 'checkbox_group';
    case FILE = 'file';
    case HIDDEN = 'hidden';
    case HEADING = 'heading';
    case PARAGRAPH = 'paragraph';

    /**
     * The base validation rule for this field type, before `is_required`/
     * `options`/`validation_rules` are layered on top.
     *
     * @return array<int, string>
     */
    public function baseRules(): array
    {
        return match ($this) {
            self::EMAIL => ['email'],
            self::NUMBER => ['numeric'],
            self::DATE => ['date'],
            self::URL => ['url'],
            self::FILE => ['file'],
            self::CHECKBOX => ['boolean'],
            default => ['string'],
        };
    }

    public function isLayoutOnly(): bool
    {
        return in_array($this, [self::HEADING, self::PARAGRAPH], true);
    }

    public function supportsOptions(): bool
    {
        return in_array($this, [self::SELECT, self::RADIO, self::CHECKBOX_GROUP], true);
    }
}
