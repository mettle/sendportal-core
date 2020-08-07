<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

class MessageOptions
{
    /** @var string */
    private $to;

    /** @var string */
    private $fromEmail;

    /** @var string */
    private $fromName;

    /** @var string */
    private $subject;

    /** @var MessageTrackingOptions */
    private $trackingOptions;

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTrackingOptions(): MessageTrackingOptions
    {
        return $this->trackingOptions;
    }

    public function setTrackingOptions(MessageTrackingOptions $trackingOptions): self
    {
        $this->trackingOptions = $trackingOptions;

        return $this;
    }
}
