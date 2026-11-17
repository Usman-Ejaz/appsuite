<?php

namespace Domains\CMS\Enums;

/**
 * The CMS events companies can attach notification actions to. Each value is a
 * `CMS:<Module>:<Event>` code; its app, label and description live in `CMSTriggerSeeder`.
 *
 * A trigger only fires once something dispatches it (see `SubmitForm` for `FORM_SUBMITTED`).
 */
enum CMSTrigger: string
{
    /**
     * A blog post was created.
     */
    case BLOG_CREATED = 'CMS:Blog:Created';

    /**
     * A blog post's content or settings were changed.
     */
    case BLOG_UPDATED = 'CMS:Blog:Updated';

    /**
     * A blog post was deleted.
     */
    case BLOG_DELETED = 'CMS:Blog:Deleted';

    /**
     * A blog post moved to another status, e.g. from Draft to Scheduled.
     */
    case BLOG_STATUS_CHANGED = 'CMS:Blog:StatusChanged';

    /**
     * A blog post went live.
     */
    case BLOG_PUBLISHED = 'CMS:Blog:Published';

    /**
     * A published blog post was taken offline.
     */
    case BLOG_UNPUBLISHED = 'CMS:Blog:Unpublished';

    /**
     * A blog post was scheduled to go live later.
     */
    case BLOG_SCHEDULED = 'CMS:Blog:Scheduled';

    /**
     * A blog post was archived.
     */
    case BLOG_ARCHIVED = 'CMS:Blog:Archived';

    /**
     * A visitor commented on a blog post.
     */
    case COMMENT_POSTED = 'CMS:Comment:Posted';

    /**
     * A comment was approved and is now visible.
     */
    case COMMENT_APPROVED = 'CMS:Comment:Approved';

    /**
     * A comment was rejected during moderation.
     */
    case COMMENT_REJECTED = 'CMS:Comment:Rejected';

    /**
     * A comment was marked as spam.
     */
    case COMMENT_MARKED_SPAM = 'CMS:Comment:MarkedSpam';

    /**
     * A comment was deleted.
     */
    case COMMENT_DELETED = 'CMS:Comment:Deleted';

    /**
     * A page was created.
     */
    case PAGE_CREATED = 'CMS:Page:Created';

    /**
     * A page's content or settings were changed.
     */
    case PAGE_UPDATED = 'CMS:Page:Updated';

    /**
     * A page was deleted.
     */
    case PAGE_DELETED = 'CMS:Page:Deleted';

    /**
     * A page went live.
     */
    case PAGE_PUBLISHED = 'CMS:Page:Published';

    /**
     * A published page was taken offline.
     */
    case PAGE_UNPUBLISHED = 'CMS:Page:Unpublished';

    /**
     * A form was created.
     */
    case FORM_CREATED = 'CMS:Form:Created';

    /**
     * A form's settings, fields or actions were changed.
     */
    case FORM_UPDATED = 'CMS:Form:Updated';

    /**
     * A form was deleted.
     */
    case FORM_DELETED = 'CMS:Form:Deleted';

    /**
     * A form was switched on and accepts submissions again.
     */
    case FORM_ACTIVATED = 'CMS:Form:Activated';

    /**
     * A form was switched off and stops accepting submissions.
     */
    case FORM_DEACTIVATED = 'CMS:Form:Deactivated';

    /**
     * Someone submitted one of the company's forms.
     */
    case FORM_SUBMITTED = 'CMS:Form:Submitted';

    /**
     * A form submission moved to another status, e.g. from New to Read.
     */
    case FORM_SUBMISSION_STATUS_CHANGED = 'CMS:FormSubmission:StatusChanged';

    /**
     * A form submission was marked as read.
     */
    case FORM_SUBMISSION_MARKED_READ = 'CMS:FormSubmission:MarkedRead';

    /**
     * A form submission was marked as spam.
     */
    case FORM_SUBMISSION_MARKED_SPAM = 'CMS:FormSubmission:MarkedSpam';

    /**
     * A form submission was deleted.
     */
    case FORM_SUBMISSION_DELETED = 'CMS:FormSubmission:Deleted';

    /**
     * A CMS category was created.
     */
    case CATEGORY_CREATED = 'CMS:Category:Created';

    /**
     * A CMS category was changed.
     */
    case CATEGORY_UPDATED = 'CMS:Category:Updated';

    /**
     * A CMS category was deleted.
     */
    case CATEGORY_DELETED = 'CMS:Category:Deleted';

    /**
     * A page or blog template was created.
     */
    case TEMPLATE_CREATED = 'CMS:Template:Created';

    /**
     * A template was changed.
     */
    case TEMPLATE_UPDATED = 'CMS:Template:Updated';

    /**
     * A template was deleted.
     */
    case TEMPLATE_DELETED = 'CMS:Template:Deleted';

    /**
     * A file was uploaded to the CMS media library.
     */
    case MEDIA_UPLOADED = 'CMS:Media:Uploaded';

    /**
     * A file was deleted from the CMS media library.
     */
    case MEDIA_DELETED = 'CMS:Media:Deleted';

    /**
     * A CMS site was created.
     */
    case SITE_CREATED = 'CMS:Site:Created';

    /**
     * A CMS site's details or settings were changed.
     */
    case SITE_UPDATED = 'CMS:Site:Updated';

    /**
     * A CMS site was deleted.
     */
    case SITE_DELETED = 'CMS:Site:Deleted';
}
