<?php

namespace Sendportal\Base\Events\Webhooks;

class ResendWebhookReceived
{
    /** @var array */
    public $payload;

    /**
     * The workspaces a contact event applies to.
     *
     * @var array
     */
    public $workspaceIds;

    public function __construct(array $payload, array $workspaceIds = [])
    {
        $this->payload = $payload;
        $this->workspaceIds = $workspaceIds;
    }
}
