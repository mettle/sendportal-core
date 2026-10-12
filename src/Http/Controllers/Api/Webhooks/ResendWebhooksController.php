<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Resend\Exceptions\WebhookSignatureVerificationException;
use Resend\WebhookSignature;
use Sendportal\Base\Events\Webhooks\ResendWebhookReceived;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Message;

class ResendWebhooksController extends Controller
{
    /**
     * Contact events which may carry an unsubscribe.
     *
     * @var array
     */
    protected $contactEvents = [
        'contact.created',
        'contact.updated',
    ];

    public function handle(Request $request): Response
    {
        /** @var array $payload */
        $payload = json_decode($request->getContent(), true) ?? [];

        Log::info('Resend webhook received');

        $type = (string) Arr::get($payload, 'type');

        // Resend signs webhooks with Svix, which rejects signatures older than five minutes. Verification
        // must therefore happen here, against the raw body, rather than in the queued listener.
        if (Str::startsWith($type, 'email.')) {
            return $this->handleEmailEvent($request, $payload);
        }

        if (in_array($type, $this->contactEvents, true)) {
            return $this->handleContactEvent($request, $payload);
        }

        return response('OK (not processed)');
    }

    protected function handleEmailEvent(Request $request, array $payload): Response
    {
        $message = Message::with('source.email_service')
            ->where('message_id', (string) Arr::get($payload, 'data.email_id'))
            ->first();

        if (! $message) {
            return response('OK (not processed)');
        }

        /** @var EmailService|null $emailService */
        $emailService = $message->source->email_service ?? null;

        if (! $this->verifySignature($request, $emailService->settings['webhook_secret'] ?? null)) {
            Log::error('Resend webhook failed verification check.', ['payload' => $payload]);

            return response('Invalid signature', 401);
        }

        event(new ResendWebhookReceived($payload));

        return response('OK');
    }

    /**
     * Contact events are not tied to a message, so the workspaces they apply to are those
     * with a Resend email service whose signing secret matches the webhook.
     */
    protected function handleContactEvent(Request $request, array $payload): Response
    {
        $workspaceIds = EmailService::where('type_id', EmailServiceType::RESEND)
            ->get()
            ->filter(function (EmailService $emailService) use ($request) {
                return $this->verifySignature($request, $emailService->settings['webhook_secret'] ?? null);
            })
            ->pluck('workspace_id')
            ->unique()
            ->values()
            ->all();

        if (! $workspaceIds) {
            Log::error('Resend webhook failed verification check.', ['payload' => $payload]);

            return response('Invalid signature', 401);
        }

        event(new ResendWebhookReceived($payload, $workspaceIds));

        return response('OK');
    }

    /**
     * Validate that the webhook came from Resend.
     */
    protected function verifySignature(Request $request, ?string $secret): bool
    {
        if (! $secret) {
            return false;
        }

        try {
            return WebhookSignature::verify($request->getContent(), [
                'svix-id' => $request->header('svix-id'),
                'svix-timestamp' => $request->header('svix-timestamp'),
                'svix-signature' => $request->header('svix-signature'),
            ], $secret);
        } catch (WebhookSignatureVerificationException $e) {
            return false;
        }
    }
}
