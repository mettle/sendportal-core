<?php

namespace Sendportal\Base\Events\Webhooks;

class SendgridWebhookReceived
{
    /** @var array */
    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
