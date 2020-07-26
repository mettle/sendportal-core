<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use DomainException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;
use SendGrid\Response as SendgridResponse;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;

class SendgridMailAdapter extends BaseMailAdapter
{
    /** @var SendGrid */
    protected $client;

    /**
     * @throws TypeException
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): ?string
    {
        $email = new Mail();
        $email->setFrom($fromName.' <'.$fromEmail.'>');
        $email->setSubject($subject);
        $email->addTo($toEmail);
        $email->addContent('text/html', $content);
        $email->setClickTracking($trackingOptions->isClickTracking());
        $email->setOpenTracking($trackingOptions->isOpenTracking());

        try {
            $response = $this->resolveClient()->send($email);
        } catch (Exception $e) {
            Log::error('Failed to send via SendGrid', ['error' => $e->getMessage()]);
            return null;
        }

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
