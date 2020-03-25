<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;

class MessageTrackingOptions
{
    /** @var bool */
    private $isOpenTracking = true;

    /** @var bool */
    private $isClickTracking = true;

    public static function fromMessage(Message $message): MessageTrackingOptions
    {
        // NOTE(david): at the moment only campaigns have the ability to turn off tracking, so we start
        // by creating a default set of options that has the tracking on, and only look to adjust that
        // if the message we've got is for a campaign.
        $trackingOptions = new static;

        if ($message->source && get_class($message->source) === Campaign::class) {
            return static::fromCampaign($message->source);
        }

        return $trackingOptions;
    }

    public static function fromCampaign(Campaign $campaign): MessageTrackingOptions
    {
        return (new static)
            ->setIsOpenTracking($campaign->is_open_tracking ?? true)
            ->setIsClickTracking($campaign->is_click_tracking ?? true);
    }

    public function isOpenTracking(): bool
    {
        return $this->isOpenTracking;
    }

    public function setIsOpenTracking(bool $isOpenTracking): self
    {
        $this->isOpenTracking = $isOpenTracking;

        return $this;
    }

    public function isClickTracking(): bool
    {
        return $this->isClickTracking;
    }

    public function setIsClickTracking(bool $isClickTracking): self
    {
        $this->isClickTracking = $isClickTracking;

        return $this;
    }
}
