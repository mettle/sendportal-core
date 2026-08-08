<?php

namespace Sendportal\Base\Adapters;

use Illuminate\Support\Arr;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SmtpAdapter extends BaseMailAdapter
{
    /** @var EsmtpTransport */
    protected $transport;

    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        // Send through the transport rather than the Mailer: Mailer::send() returns
        // void, so the message id was previously always '-1'. The transport hands
        // back a SentMessage carrying the real id.
        //
        // A TransportException is deliberately not caught here. Swallowing it
        // returned '-1', which every caller reads as a truthy success, so failed
        // sends were recorded as delivered. The other adapters let it propagate.
        $sentMessage = $this->resolveTransport()->send(
            $this->resolveMessage($subject, $content, $fromEmail, $fromName, $toEmail)
        );

        return $this->resolveMessageId($sentMessage);
    }

    protected function resolveTransport(): EsmtpTransport
    {
        if ($this->transport) {
            return $this->transport;
        }

        $factory = new EsmtpTransportFactory();

        $encryption = Arr::get($this->config, 'encryption');

        $scheme = ! is_null($encryption) && $encryption === 'tls'
            ? ((Arr::get($this->config, 'port') == 465) ? 'smtps' : 'smtp')
            : '';

        $dsn = new Dsn(
            $scheme,
            Arr::get($this->config, 'host'),
            Arr::get($this->config, 'username'),
            Arr::get($this->config, 'password'),
            Arr::get($this->config, 'port')
        );

        $this->transport = $factory->create($dsn);

        return $this->transport;
    }

    protected function resolveMessage(string $subject, string $content, string $fromEmail, string $fromName, string $toEmail): Email
    {
        $msg = (new Email())
            ->from(new Address($fromEmail, $fromName))
            ->to($toEmail)
            ->subject($subject)
            ->html($content);

        return $msg;
    }

    protected function resolveMessageId($result): string
    {
        return ($result instanceof SentMessage) ? $result->getMessageId() : '-1';
    }
}
