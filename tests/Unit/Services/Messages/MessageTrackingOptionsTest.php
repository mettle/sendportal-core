<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Messages;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Tests\TestCase;

class MessageTrackingOptionsTest extends TestCase
{
    /** @test */
    function default_tracking_options_are_on()
    {
        // given
        $trackingOptions = new MessageTrackingOptions();

        // then
        $this->assertTrue($trackingOptions->isOpenTracking());
        $this->assertTrue($trackingOptions->isClickTracking());
    }

    /** @test */
    function open_tracking_can_be_turned_off()
    {
        // given
        $trackingOptions = (new MessageTrackingOptions)->setIsOpenTracking(false);

        // then
        $this->assertFalse($trackingOptions->isOpenTracking());
    }

    /** @test */
    function click_tracking_can_be_turned_off()
    {
        // given
        $trackingOptions = (new MessageTrackingOptions)->setIsClickTracking(false);

        // then
        $this->assertFalse($trackingOptions->isClickTracking());
    }

    /** @test */
    function tracking_can_be_turned_off_entirely()
    {
        // given
        $trackingOptions = (new MessageTrackingOptions)->snooze();

        // then
        $this->assertFalse($trackingOptions->isClickTracking());
        $this->assertFalse($trackingOptions->isOpenTracking());
    }

    /** @test */
    function open_tracking_can_be_turned_off_from_a_campaign()
    {
        // given
        $campaign = factory(Campaign::class)->state('withoutOpenTracking')->make();

        // when
        $trackingOptions = MessageTrackingOptions::fromCampaign($campaign);

        // then
        $this->assertFalse($trackingOptions->isOpenTracking());
        $this->assertTrue($trackingOptions->isClickTracking());
    }

    /** @test */
    function click_tracking_can_be_turned_off_from_a_campaign()
    {
        // given
        $campaign = factory(Campaign::class)->state('withoutClickTracking')->make();

        // when
        $trackingOptions = MessageTrackingOptions::fromCampaign($campaign);

        // then
        $this->assertTrue($trackingOptions->isOpenTracking());
        $this->assertFalse($trackingOptions->isClickTracking());
    }

    /** @test */
    function open_tracking_can_be_turned_off_from_a_message()
    {
        // given
        $campaign = factory(Campaign::class)->state('withoutOpenTracking')->make();
        $message = new Message();
        $message->source = $campaign;

        // when
        $trackingOptions = MessageTrackingOptions::fromMessage($message);

        // then
        $this->assertFalse($trackingOptions->isOpenTracking());
        $this->assertTrue($trackingOptions->isClickTracking());
    }

    /** @test */
    function click_tracking_can_be_turned_off_from_a_message()
    {
        // given
        $campaign = factory(Campaign::class)->state('withoutClickTracking')->make();
        $message = new Message();
        $message->source = $campaign;

        // when
        $trackingOptions = MessageTrackingOptions::fromMessage($message);

        // then
        $this->assertTrue($trackingOptions->isOpenTracking());
        $this->assertFalse($trackingOptions->isClickTracking());
    }
}
