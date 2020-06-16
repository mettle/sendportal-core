<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\ElasticWebhookReceived;
use Sendportal\Base\Http\Controllers\Controller;

class ElasticWebhooksController extends Controller
{
    public function handle(): Response
    {
        /** @var array $payload */
        $payload = request()->only('date', 'status', 'category', 'messageid', 'target');

        Log::info('ElasticEmail webhook received');

        if (count($payload)) {
            event(new ElasticWebhookReceived($payload));

            return response('OK');
        }

        return response('OK (not processed');
    }
}
