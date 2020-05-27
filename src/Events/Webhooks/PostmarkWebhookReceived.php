<?php

namespace Sendportal\Base\Events\Webhooks;

class PostmarkWebhookReceived
{
    /** @var array */
    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
