<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use Illuminate\Support\Arr;
use Resend;
use Resend\Client;
use Resend\Email;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;

class ResendAdapter extends BaseMailAdapter
{
    /** @var Client */
    protected $client;

    /**
     * Open and click tracking are configured per domain in the Resend dashboard,
     * so the tracking options cannot be applied on a per-message basis.
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        $result = $this->resolveClient()->emails->send([
            'from' => "{$fromName} <{$fromEmail}>",
            'to' => [$toEmail],
            'subject' => $subject,
            'html' => $content,
        ]);

        return $this->resolveMessageId($result);
    }

    protected function resolveClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = Resend::client(Arr::get($this->config, 'key'));

        return $this->client;
    }

    protected function resolveMessageId(Email $result): string
    {
        return $result->id;
    }
}
