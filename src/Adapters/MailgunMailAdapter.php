<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Mailgun\Mailgun;
use Mailgun\Model\Message\SendResponse;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;

class MailgunMailAdapter extends BaseMailAdapter
{
    /** @var Mailgun */
    protected $client;

    protected $urls = [
        'US' => 'https://api.mailgun.net/v3',
        'EU' => 'https://api.eu.mailgun.net/v3'
    ];

    public function send(string $fromEmail, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        $parameters = [
            'from' => $fromEmail,
            'to' => $toEmail,
            'subject' => $subject,
            'html' => $content,
            'o:tracking-clicks' => (string) $trackingOptions->isClickTracking(),
            'o:tracking-opens' => (string) $trackingOptions->isOpenTracking()
        ];

        $result = $this->resolveClient()->messages()->send($this->resolveDomain(), $parameters);

        return $this->resolveMessageId($result);
    }

    protected function resolveClient(): Mailgun
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = Mailgun::create(Arr::get($this->config, 'key'), $this->resolveZone());

        return $this->client;
    }

    protected function resolveDomain(): string
    {
        return Arr::get($this->config, 'domain');
    }

    protected function resolveZone(): string
    {
        return $this->urls[Arr::get($this->config, 'zone', 'US')];
    }

    protected function resolveMessageId(SendResponse $result): string
    {
        return $result->getId();
    }
}
