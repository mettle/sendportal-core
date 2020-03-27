<?php

namespace Sendportal\Base\Events\Webhooks;

class PostmarkWebhookEvent
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
