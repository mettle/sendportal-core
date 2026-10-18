<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use DomainException;
use Illuminate\Support\Arr;
use Postal\Client;
use Postal\Send\Message;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;

class PostalAdapter extends BaseMailAdapter
{
    /** @var Client */
    protected $client;

    /**
     * @throws TypeException
     * @throws \Throwable
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content, array $headers = []): string
    {
        $message = new Message();
        $message->to($toEmail);
        $message->from($fromName.' <'.$fromEmail.'>');
        $message->subject($subject);
        $message->htmlBody($content);

        foreach ($headers as $name => $value) {
            $message->header($name, $value);
        }

        $response = $this->resolveClient()->send->message($message);

        return $this->resolveMessageId($response);
    }

    protected function resolveClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client('https://' . Arr::get($this->config, 'postal_host'), Arr::get($this->config, 'key'));

        return $this->client;
    }

    protected function resolveMessageId($response): string
    {
        foreach ($response->recipients() as $email => $message) {
            return (string) $message->id;
        }

        throw new DomainException('Unable to resolve message ID');
    }
}
