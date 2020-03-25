<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Sendportal\Base\Events\Webhooks\PostmarkWebhookEvent;
use Sendportal\Base\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PostmarkWebhooksController extends Controller
{
    public function handle(): Response
    {
        /** @var array $payload */
        $payload = json_decode(request()->getContent(), true);

        Log::info('Postmark webhook received');

        event(new PostmarkWebhookEvent($payload));

        return response('OK');
    }
}
