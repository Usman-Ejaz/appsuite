<?php

namespace Domains\Storage\Enums;

enum MediaCategory: string
{
    case AVATAR = 'Avatar';
    case LOGO = 'Logo';
    case THUMBNAIL = 'Thumbnail';
    case GALLERY = 'Gallery';
    case BANNER = 'Banner';
    case DOCUMENT = 'Document';
    case ATTACHMENT = 'Attachment';
    case OTHER = 'Other';

    /**
     * The file extensions an upload in this category is allowed to have.
     *
     * @return array<int, string>
     */
    public function allowedExtensions(): array
    {
        return match ($this) {
            self::AVATAR, self::LOGO, self::THUMBNAIL, self::GALLERY, self::BANNER => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            self::DOCUMENT => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt'],
            self::ATTACHMENT, self::OTHER => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip'],
        };
    }

    /**
     * The maximum upload size allowed for this category, in kilobytes.
     */
    public function maxSizeInKilobytes(): int
    {
        return match ($this) {
            self::AVATAR, self::LOGO, self::THUMBNAIL => 5_120,
            self::GALLERY, self::BANNER => 10_240,
            self::DOCUMENT => 20_480,
            self::ATTACHMENT, self::OTHER => 25_600,
        };
    }
}
