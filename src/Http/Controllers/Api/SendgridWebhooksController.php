<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Sendportal\Base\Events\Webhooks\SendgridWebhookEvent;
use Sendportal\Base\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SendgridWebhooksController extends Controller
{
    public function handle(): Response
    {
        /** @var array $payload */
        $payload = json_decode(request()->getContent(), true);

        Log::info('SendGrid webhook received');

        foreach (Arr::get($payload, null, []) as $event) {
            event(new SendgridWebhookEvent($event));

            return response('OK');
        }

        return response('OK (not processed');
    }
}
