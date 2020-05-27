<?php

namespace Sendportal\Base\Models;

class CampaignStatus extends BaseModel
{
    
    /**
     * @var bool
     */
    public $timestamps = false;

    const STATUS_DRAFT = 1;
    const STATUS_QUEUED = 2;
    const STATUS_SENDING = 3;
    const STATUS_SENT = 4;
}
