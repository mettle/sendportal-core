<?php

namespace Tests\Unit\Models;

use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_has_many_opens()
    {
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        $openedMessages = $this->createOpenedMessage($campaign, 3);
        $this->createUnopenedMessage($campaign, 2);

        $opens = $campaign->opens;

        $opens->each(function ($open) use ($openedMessages)
        {
            $validMessages = $openedMessages->pluck('id')->toArray();

            static::assertTrue(in_array($open->id, $validMessages));
        });
        static::assertEquals(3, $opens->count());
    }

    /** @test */
    function the_unique_open_count_attribute_returns_the_number_of_unique_opens_for_a_campaign()
    {
        $emailService = $this->createEmailService();

        $campaign = factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);
        $this->createOpenedMessage($campaign, 3);

        static::assertEquals(3, $campaign->unique_open_count);
    }

    /** @test */
    function the_total_open_count_attribute_returns_the_total_number_of_opens_for_a_campaign()
    {
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);
        $this->createOpenedMessage($campaign, 3, [
            'open_count' => 5
        ]);

        static::assertEquals(15, $campaign->total_open_count);
    }

    /** @test */
    function it_has_many_clicks()
    {
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);
        $clickedMessages = $this->createClickedMessage($campaign, 3);
        $this->createUnclickedMessage($campaign, 2);

        $clicks = $campaign->clicks;

        $clicks->each(function ($click) use ($clickedMessages)
        {
            $validMessages = $clickedMessages->pluck('id')->toArray();

            static::assertTrue(in_array($click->id, $validMessages));
        });
        static::assertEquals(3, $clicks->count());
    }

    /** @test */
    function the_unique_click_count_attribute_returns_the_number_of_unique_clicks_for_a_campaign()
    {
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);
        $this->createClickedMessage($campaign, 3);

        static::assertEquals(3, $campaign->unique_click_count);
    }

    /** @test */
    function the_total_click_count_attribute_returns_the_total_number_of_clicks_for_a_campaign()
    {
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);
        $this->createClickedMessage($campaign, 3, [
            'click_count' => 5,
        ]);

        static::assertEquals(15, $campaign->total_click_count);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return mixed
     */
    protected function createOpenedMessage(Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'opened_at' => now(),
        ], $overrides);

        return factory(Message::class, $quantity)->create($data);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $count
     *
     * @return mixed
     */
    protected function createUnopenedMessage(Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return mixed
     */
    protected function createClickedMessage(Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'clicked_at' => now(),
        ], $overrides);

        return factory(Message::class, $quantity)->create($data);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $count
     *
     * @return mixed
     */
    protected function createUnclickedMessage(Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }
}
