<?php

namespace Sendportal\Base\Adapters;

use Illuminate\Support\Arr;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_TransportException;

class SmtpAdapter extends BaseMailAdapter
{
    /** @var Swift_Mailer */
    protected $client;

    /** @var Swift_SmtpTransport */
    protected $transport;

    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        $failedRecipients = [];

        try {
            $result = $this->resolveClient()->send($this->resolveMessage( $subject,  $content, $fromEmail,  $fromName,  $toEmail), $failedRecipients);
        } catch(Swift_TransportException $e) {
            return $this->resolveMessageId(0);
        }

        return $this->resolveMessageId($result);
    }

    protected function resolveClient(): Swift_Mailer
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Swift_Mailer($this->resolveTransport());

        return $this->client;
    }

    protected function resolveTransport(): Swift_SmtpTransport
    {
        if ($this->transport) {
            return $this->transport;
        }

        $this->transport = new Swift_SmtpTransport(
            Arr::get($this->config, 'host'),
            Arr::get($this->config, 'port'),
            Arr::get($this->config, 'encryption')
        );

        $this->transport->setUsername(Arr::get($this->config, 'username'));
        $this->transport->setPassword(Arr::get($this->config, 'password'));
        $this->transport->setAuthMode('login');

        return $this->transport;
    }

    protected function resolveMessage(string $subject, string $content,string $fromEmail, string $fromName, string $toEmail): Swift_Message
    {
        $msg = new Swift_Message($subject, $content, 'text/html');

        $msg->setTo($toEmail);
        $msg->setFrom($fromEmail, $fromName);

        return $msg;
    }

    protected function resolveMessageId($result): string
    {
        return ($result == 1) ? strval($result) : '-1';
    }
}