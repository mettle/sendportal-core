<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Illuminate\Support\Arr;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\PostalWebhookReceived;
use Sendportal\Base\Http\Controllers\Controller;

class PostalWebhooksController extends Controller
{
    public function handle(): Response
    {
        $payload = json_decode(request()->getContent(), true);

        Log::info('Postal webhook received');

        event(new PostalWebhookReceived($payload));
        

        return response('OK');
    }
}
