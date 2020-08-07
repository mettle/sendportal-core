<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;
use SendGrid\Response as SendgridResponse;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Symfony\Component\HttpFoundation\Response;

class SendgridMailAdapter extends BaseMailAdapter
{
    /** @var SendGrid */
    protected $client;

    /**
     * @throws TypeException
     * @throws \Throwable
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        $email = new Mail();
        $email->setFrom($fromEmail, $fromName);
        $email->setSubject($subject);
        $email->addTo($toEmail);
        $email->addContent('text/html', $content);
        $email->setClickTracking($trackingOptions->isClickTracking());
        $email->setOpenTracking($trackingOptions->isOpenTracking());

        $response = $this->resolveClient()->send($email);

        throw_if(
            !in_array($response->statusCode(), [Response::HTTP_OK, Response::HTTP_ACCEPTED]),
            new DomainException($response->body(), $response->statusCode())
        );

        return $this->resolveMessageId($response);
    }

    protected function resolveClient(): SendGrid
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new SendGrid(Arr::get($this->config, 'key'));

        return $this->client;
    }

    protected function resolveMessageId(SendgridResponse $response): string
    {
        foreach ($response->headers() as $header) {
            if (Str::startsWith($header, 'X-Message-Id:')) {
                return str_replace('X-Message-Id: ', '', $header);
            }
        }

        throw new DomainException('Unable to resolve message ID');
    }
}
