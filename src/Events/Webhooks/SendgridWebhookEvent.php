<?php

namespace Sendportal\Base\Events\Webhooks;

class SendgridWebhookEvent
{
    /**
     * @var array
     */
    public $payload;

    /**
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
