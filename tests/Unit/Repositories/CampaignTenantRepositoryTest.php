<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Tests\TestCase;

class CampaignTenantRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @var CampaignTenantRepositoryInterface */
    protected $campaignRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->campaignRepository = app(CampaignTenantRepositoryInterface::class);
    }

    /** @test */
    function the_get_average_time_to_open_method_returns_the_average_time_taken_to_open_a_campaigns_message()
    {
        // given
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        // 30 seconds
        $this->createOpenedMessages($campaign, 1, [
            'delivered_at' => now(),
            'opened_at' => now()->addSeconds(30),
        ]);

        // 60 seconds
        $this->createOpenedMessages($campaign, 1, [
            'delivered_at' => now(),
            'opened_at' => now()->addSeconds(60),
        ]);

        // when
        $averageTimeToOpen = $this->campaignRepository->getAverageTimeToOpen($campaign);

        // then
        // 45 seconds
        static::assertEquals('00:00:45', $averageTimeToOpen);
    }

    /** @test */
    function the_get_average_time_to_open_method_returns_na_if_there_have_been_no_opens()
    {
        // given
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        // when
        $averageTimeToOpen = $this->campaignRepository->getAverageTimeToOpen($campaign);

        // then
        static::assertEquals('N/A', $averageTimeToOpen);
    }

    /** @test */
    function the_get_average_time_to_click_method_returns_the_average_time_taken_for_a_campaign_link_to_be_clicked_for_the_first_time()
    {
        // given
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        // 30 seconds
        $this->createClickedMessage($campaign, 1, [
            'delivered_at' => now(),
            'clicked_at' => now()->addSeconds(30),
        ]);

        // 30 seconds
        $this->createClickedMessage($campaign, 1, [
            'delivered_at' => now(),
            'clicked_at' => now()->addSeconds(60),
        ]);

        // when
        $averageTimeToClick = $this->campaignRepository->getAverageTimeToClick($campaign);

        // then
        static::assertEquals('00:00:45', $averageTimeToClick);
    }

    /** @test */
    function the_average_time_to_click_attribute_returns_na_if_there_have_been_no_clicks()
    {
        // given
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        // when
        $averageTimeToClick = $this->campaignRepository->getAverageTimeToClick($campaign);

        // then
        static::assertEquals('N/A', $averageTimeToClick);
    }

    /** @test */
    function the_cancel_campaign_method_sets_the_campaign_status_to_cancelled()
    {
        // given
        $campaign = Campaign::factory()->queued()->create();

        static::assertEquals(CampaignStatus::STATUS_QUEUED, $campaign->status_id);

        // when
        $success = $this->campaignRepository->cancelCampaign($campaign);

        // then
        static::assertTrue($success);
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->fresh()->status_id);
    }

    /** @test */
    function the_cancel_campaign_method_deletes_draft_messages_if_the_campaign_has_any()
    {
        // given
        $emailService = $this->createEmailService();

        $campaign = Campaign::factory()->withContent()->sent()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
            'save_as_draft' => 1,
        ]);

        $this->createPendingMessages($campaign, 1);

        static::assertCount(1, Message::all());

        // when
        $this->campaignRepository->cancelCampaign($campaign);

        // then
        static::assertCount(0, Message::all());
    }

    /** @test */
    function the_cancel_campaign_method_does_not_delete_sent_messages()
    {
        // given
        $emailService = $this->createEmailService();

        $campaign = Campaign::factory()->withContent()->sent()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
            'save_as_draft' => 1,
        ]);

        $this->createOpenedMessages($campaign, 1);

        static::assertCount(1, Message::all());

        // when
        $this->campaignRepository->cancelCampaign($campaign);

        // then
        static::assertCount(1, Message::all());
    }

    /** @test */
    function the_get_count_method_returns_campaign_message_counts()
    {
        // given
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        $expectedOpenedMessages = 1;
        $expectedUnopenedMessages = 2;
        $expectedClickedMessages = 3;
        $expectedBouncedMessages = 4;
        $expectedPendingMessages = 5;

        $this->createOpenedMessages($campaign, $expectedOpenedMessages);
        $this->createUnopenedMessages($campaign, $expectedUnopenedMessages);
        $this->createClickedMessages($campaign, $expectedClickedMessages);
        $this->createBouncedMessages($campaign, $expectedBouncedMessages);
        $this->createPendingMessages($campaign, $expectedPendingMessages);

        // when
        $counts = $this->campaignRepository->getCounts(collect($campaign->id), Sendportal::currentWorkspaceId());

        // then
        $totalSentCount = $expectedOpenedMessages
            + $expectedClickedMessages
            + $expectedUnopenedMessages
            + $expectedBouncedMessages;

        static::assertEquals($expectedOpenedMessages, $counts[$campaign->id]->opened);
        static::assertEquals($expectedClickedMessages, $counts[$campaign->id]->clicked);
        static::assertEquals($totalSentCount, $counts[$campaign->id]->sent);
        static::assertEquals($expectedBouncedMessages, $counts[$campaign->id]->bounced);
        static::assertEquals($expectedPendingMessages, $counts[$campaign->id]->pending);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createCampaign(EmailService $emailService): Campaign
    {
        return Campaign::factory()->withContent()->sent()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createOpenedMessages(Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'opened_at' => now(),
        ], $overrides);

        return Message::factory()->count($quantity)->create($data);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createUnopenedMessages(Campaign $campaign, int $count)
    {
        return Message::factory()->count($count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createClickedMessage(Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'clicked_at' => now(),
        ], $overrides);

        return Message::factory()->count($quantity)->create($data);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createUnclickedMessage(Campaign $campaign, int $count)
    {
        return Message::factory()->count($count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createBouncedMessages(Campaign $campaign, int $count)
    {
        return Message::factory()->count($count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'sent_at' => now(),
            'bounced_at' => now(),
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createPendingMessages(Campaign $campaign, int $count)
    {
        return Message::factory()->count($count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'sent_at' => null,
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createClickedMessages(Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'clicked_at' => now(),
        ], $overrides);

        return Message::factory()->count($quantity)->create($data);
    }
}
