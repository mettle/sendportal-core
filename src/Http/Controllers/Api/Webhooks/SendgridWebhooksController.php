<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\SendgridWebhookEvent;
use Sendportal\Base\Http\Controllers\Controller;

class SendgridWebhooksController extends Controller
{
    public function handle(): Response
    {
        $payload = collect(json_decode(request()->getContent(), true));

        Log::info('SendGrid webhook received');

        if ($payload->isEmpty()) {
            return response('OK (not processed');
        }

        foreach ($payload as $event) {
            event(new SendgridWebhookEvent($event));
        }

        return response('OK');
    }
}
