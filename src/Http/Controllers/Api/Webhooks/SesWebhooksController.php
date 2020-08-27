<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\SesWebhookReceived;
use Sendportal\Base\Http\Controllers\Controller;

class SesWebhooksController extends Controller
{
    /**
     * @throws Exception
     */
    public function handle(): Response
    {
        $payload = json_decode(request()->getContent(), true);

        Log::info('SES webhook received', ['payload' => $payload]);

        $payloadType = $payload['eventType'] ?? null;

        event(new SesWebhookReceived($payload, $payloadType));

        return response('OK');
    }
}
