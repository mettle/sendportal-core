<?php

namespace Sendportal\Base\Events\Webhooks;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ElasticWebhookReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var array */
    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
