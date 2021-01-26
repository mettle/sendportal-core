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
    public function default_tracking_options_are_on()
    {
        // given
        $trackingOptions = new MessageTrackingOptions();

        // then
        self::assertTrue($trackingOptions->isOpenTracking());
        self::assertTrue($trackingOptions->isClickTracking());
    }

    /** @test */
    public function open_tracking_can_be_turned_off()
    {
        // given
        $trackingOptions = (new MessageTrackingOptions)->setIsOpenTracking(false);

        // then
        self::assertFalse($trackingOptions->isOpenTracking());
    }

    /** @test */
    public function click_tracking_can_be_turned_off()
    {
        // given
        $trackingOptions = (new MessageTrackingOptions)->setIsClickTracking(false);

        // then
        self::assertFalse($trackingOptions->isClickTracking());
    }

    /** @test */
    public function tracking_can_be_turned_off_entirely()
    {
        // given
        $trackingOptions = (new MessageTrackingOptions)->disable();

        // then
        $this->assertFalse($trackingOptions->isClickTracking());
        $this->assertFalse($trackingOptions->isOpenTracking());
    }

    /** @test */
    public function open_tracking_can_be_turned_off_from_a_campaign()
    {
        // given
        $campaign = Campaign::factory()->withoutOpenTracking()->make();

        // when
        $trackingOptions = MessageTrackingOptions::fromCampaign($campaign);

        // then
        self::assertFalse($trackingOptions->isOpenTracking());
        self::assertTrue($trackingOptions->isClickTracking());
    }

    /** @test */
    public function click_tracking_can_be_turned_off_from_a_campaign()
    {
        // given
        $campaign = Campaign::factory()->withoutClickTracking()->make();

        // when
        $trackingOptions = MessageTrackingOptions::fromCampaign($campaign);

        // then
        self::assertTrue($trackingOptions->isOpenTracking());
        self::assertFalse($trackingOptions->isClickTracking());
    }

    /** @test */
    public function open_tracking_can_be_turned_off_from_a_message()
    {
        // given
        $campaign = Campaign::factory()->withoutOpenTracking()->make();
        $message = new Message();
        $message->source = $campaign;

        // when
        $trackingOptions = MessageTrackingOptions::fromMessage($message);

        // then
        self::assertFalse($trackingOptions->isOpenTracking());
        self::assertTrue($trackingOptions->isClickTracking());
    }

    /** @test */
    public function click_tracking_can_be_turned_off_from_a_message()
    {
        // given
        $campaign = Campaign::factory()->withoutClickTracking()->make();
        $message = new Message();
        $message->source = $campaign;

        // when
        $trackingOptions = MessageTrackingOptions::fromMessage($message);

        // then
        self::assertTrue($trackingOptions->isOpenTracking());
        self::assertFalse($trackingOptions->isClickTracking());
    }
}
