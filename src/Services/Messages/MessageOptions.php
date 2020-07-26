<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

class MessageOptions
{
    /** @var string */
    private $to;

    /** @var string */
    private $from;

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
    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

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
