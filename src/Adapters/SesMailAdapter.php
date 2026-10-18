<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use Aws\Result;
use Aws\Ses\SesClient;
use Aws\SesV2\SesV2Client;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Sendportal\Base\Traits\ThrottlesSending;

class SesMailAdapter extends BaseMailAdapter
{
    use ThrottlesSending;

    /** @var SesClient */
    protected $client;

    /** @var SesV2Client */
    protected $v2Client;

    /**
     * Sent through the SESv2 SendEmail operation, because the v1 operation cannot set
     * custom headers such as List-Unsubscribe. Both need the ses:SendEmail permission.
     *
     * @throws BindingResolutionException
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content, array $headers = []): string
    {
        // TODO(david): It isn't clear whether it is possible to set per-message tracking for SES.

        $message = [
            'Subject' => [
                'Data' => $subject,
            ],
            'Body' => [
                'Html' => [
                    'Data' => $content,
                ],
            ],
        ];

        foreach ($headers as $name => $value) {
            $message['Headers'][] = ['Name' => $name, 'Value' => $value];
        }

        $result = $this->throttleSending(function () use ($fromEmail, $fromName, $toEmail, $message) {
            return $this->resolveV2Client()->sendEmail([
                'FromEmailAddress' => $fromName . ' <' . $fromEmail . '>',

                'Destination' => [
                    'ToAddresses' => [$toEmail],
                ],

                'Content' => [
                    'Simple' => $message,
                ],
                'ConfigurationSetName' => Arr::get($this->config, 'configuration_set_name'),
            ]);
        });

        return $this->resolveMessageId($result);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function resolveClient(): SesClient
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = app()->make('aws')->createClient('ses', [
            'region' => Arr::get($this->config, 'region'),
            'credentials' => [
                'key' => Arr::get($this->config, 'key'),
                'secret' => Arr::get($this->config, 'secret'),
            ]
        ]);

        return $this->client;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function resolveV2Client(): SesV2Client
    {
        if ($this->v2Client) {
            return $this->v2Client;
        }

        $this->v2Client = app()->make('aws')->createClient('sesv2', [
            'region' => Arr::get($this->config, 'region'),
            'credentials' => [
                'key' => Arr::get($this->config, 'key'),
                'secret' => Arr::get($this->config, 'secret'),
            ]
        ]);

        return $this->v2Client;
    }

    protected function resolveMessageId(Result $result): string
    {
        return Arr::get($result->toArray(), 'MessageId');
    }

    /**
     * https://docs.aws.amazon.com/ses/latest/APIReference/API_GetSendQuota.html
     *
     * @throws BindingResolutionException
     */
    public function getSendQuota(): array
    {
        return $this->resolveClient()->getSendQuota()->toArray();
    }
}
