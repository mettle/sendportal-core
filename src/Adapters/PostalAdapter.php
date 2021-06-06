<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Postal\Client;
use Postal\SendMessage;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Symfony\Component\HttpFoundation\Response;

class PostalAdapter extends BaseMailAdapter
{
  
    /**
     * @throws TypeException
     * @throws \Throwable
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        $client = new Client('https://' . Arr::get($this->config, 'domain'), Arr::get($this->config, 'key'));
        
        $message = new SendMessage($client);
        $message->to($toEmail);
        $message->from($fromName.' <'.$fromEmail.'>');
        $message->subject($subject);
        $message->htmlBody($content);
        $response = $message->send();
        
        /*
        throw_if(
            !in_array($response->statusCode(), [Response::HTTP_OK, Response::HTTP_ACCEPTED]),
            new DomainException($response->body(), $response->statusCode())
        );
        */
        return $this->resolveMessageId($response);
    }

   

    protected function resolveMessageId($response): string
    {
        foreach ($response->recipients() as $email => $message) {
            return (string) $message->id();    
        }
        

        throw new DomainException('Unable to resolve message ID');
    }
}
