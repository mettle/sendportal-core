<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Sendportal\Base\Events\Webhooks\MailgunWebhookEvent;
use Sendportal\Base\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MailgunWebhooksController extends Controller
{
    public function handle(): Response
    {
        /** @var array $payload */
        $payload = json_decode(request()->getContent(), true);

        $payload = $this->stripAttachments($payload);

        Log::info('Mailgun webhook received');

        if (array_get($payload, 'event-data.event')) {
            event(new MailgunWebhookEvent($payload));

            return response('OK');
        }

        return response('OK (not processed');
    }

    /**
     * Remove attachments from the webhook.
     *
     * This is needed to ensure that the payload can be correctly serialized for the queue.
     */
    protected function stripAttachments(array $payload): array
    {
        unset($payload['event-data.message.attachments']);

        return $payload;
    }
}
