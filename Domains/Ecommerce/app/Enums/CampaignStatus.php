<?php

namespace Domains\Ecommerce\Enums;

/**
 * The publication state of a marketing campaign.
 */
enum CampaignStatus: string
{
    /**
     * The campaign is being prepared and isn't live yet.
     */
    case DRAFT = 'Draft';

    /**
     * The campaign is live and currently running.
     */
    case ACTIVE = 'Active';

    /**
     * The campaign has finished running.
     */
    case ENDED = 'Ended';
}
