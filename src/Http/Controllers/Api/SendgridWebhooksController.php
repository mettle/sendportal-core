<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\SendgridWebhookEvent;
use Sendportal\Base\Http\Controllers\Controller;

class SendgridWebhooksController extends Controller
{
    public function handle(): Response
    {
        /** @var array $payload */
        $payload = json_decode(request()->getContent(), true);

        Log::info('SendGrid webhook received');

        $event = collect($payload)->first();

        if (!$event) {
            return response('OK (not processed');
        }

        event(new SendgridWebhookEvent($event));

        return response('OK');
    }
}
