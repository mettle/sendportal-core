<?php

declare(strict_types=1);

namespace Sendportal\Base\Events\Webhooks;

class SesWebhookReceived
{
    /** @var array */
    public $payload;

    /** @var string */
    public $payloadType;

    public function __construct(array $payload, string $payloadType)
    {
        $this->payload = $payload;
        $this->payloadType = $payloadType;
    }
}
