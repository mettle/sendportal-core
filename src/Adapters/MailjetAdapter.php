<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use Illuminate\Support\Arr;
use Mailjet\Client;
use Mailjet\Resources;
use Mailjet\Response;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;

class MailjetAdapter extends BaseMailAdapter
{
    /** @var Client */
    protected $client;

    protected $urls = [
        'Default' => 'api.mailjet.com',
        'US' => 'api.us.mailjet.com'
    ];

    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content,string $replyToEmail): string
    {
        $response = $this->resolveClient()->post(Resources::$Email, [
            'body' => [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => $fromEmail,
                            'Name' => $fromName
                        ],
                        'To' => [
                            [
                                'Email' => $toEmail,
                                // 'Name' => ''
                            ]
                        ],
                        'Subject' => $subject,
                        'HTMLPart' => $content,
                    ]
                ],
                'TrackOpens' => $trackingOptions->isOpenTracking() ? 'enabled' : 'disabled',
                'TrackClicks' => $trackingOptions->isClickTracking() ? 'enabled' : 'disabled'
            ]
        ]);

        if ($response->success()) {
            return $this->resolveMessageId($response);
        }

        return '';
    }

    protected function resolveClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client(
            Arr::get($this->config, 'key'),
            Arr::get($this->config, 'secret'),
            app()->environment() !== 'testing',
            [
                'version' => 'v3.1',
                'url' => $this->resolveUrl()
            ]
        );

        return $this->client;
    }

    protected function resolveUrl(): string
    {
        return $this->urls[Arr::get($this->config, 'zone', 'Default')];
    }

    protected function resolveMessageId(Response $response): string
    {
        return (string) Arr::get($response->getData(), 'Messages.0.To.0.MessageID');
    }
}
