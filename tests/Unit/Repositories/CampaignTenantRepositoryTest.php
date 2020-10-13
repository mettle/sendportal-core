<?php

namespace Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
    public function the_get_average_time_to_open_method_returns_the_average_time_taken_to_open_a_campaigns_message()
    {
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

        $averageTimeToOpen = $this->campaignRepository->getAverageTimeToOpen($campaign);

        // 45 seconds
        static::assertEquals('00:00:45', $averageTimeToOpen);
    }

    /** @test */
    public function the_get_average_time_to_open_method_returns_na_if_there_have_been_no_opens()
    {
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        $averageTimeToOpen = $this->campaignRepository->getAverageTimeToOpen($campaign);

        static::assertEquals('N/A', $averageTimeToOpen);
    }

    /** @test */
    public function the_get_average_time_to_click_method_returns_the_average_time_taken_for_a_campaign_link_to_be_clicked_for_the_first_time()
    {
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

        $averageTimeToClick = $this->campaignRepository->getAverageTimeToClick($campaign);

        static::assertEquals('00:00:45', $averageTimeToClick);
    }

    /** @test */
    public function the_average_time_to_click_attribute_returns_na_if_there_have_been_no_clicks()
    {
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        $averageTimeToClick = $this->campaignRepository->getAverageTimeToClick($campaign);

        static::assertEquals('N/A', $averageTimeToClick);
    }

    /** @test */
    public function the_cancel_campaign_method_sets_the_campaign_status_to_cancelled()
    {
        $campaign = factory(Campaign::class)->state('queued')->create();

        static::assertEquals(CampaignStatus::STATUS_QUEUED, $campaign->status_id);
        $success = $this->campaignRepository->cancelCampaign($campaign);

        static::assertTrue($success);
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->fresh()->status_id);
    }

    /** @test */
    public function the_cancel_campaign_method_deletes_draft_messages_if_the_campaign_has_any()
    {
        $emailService = $this->createEmailService();

        $campaign = factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
            'save_as_draft' => 1,
        ]);
        $this->createPendingMessages($campaign, 1);

        static::assertCount(1, Message::all());

        $this->campaignRepository->cancelCampaign($campaign);

        static::assertCount(0, Message::all());
    }

    /** @test */
    public function the_cancel_campaign_method_does_not_delete_sent_messages()
    {
        $emailService = $this->createEmailService();

        $campaign = factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
            'save_as_draft' => 1,
        ]);
        $this->createOpenedMessages($campaign, 1);

        static::assertCount(1, Message::all());

        $this->campaignRepository->cancelCampaign($campaign);

        static::assertCount(1, Message::all());
    }

    /** @test */
    public function the_get_count_method_returns_campaign_message_counts()
    {
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

        $counts = $this->campaignRepository->getCounts(collect($campaign->id), Sendportal::currentWorkspaceId());

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
     * @param EmailService $emailService
     * @return Campaign
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createCampaign(EmailService $emailService): Campaign
    {
        return factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);
    }

    /**
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return Collection|Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createOpenedMessages(Campaign $campaign, int $quantity = 1, array $overrides = [])
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
     * @param Campaign $campaign
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createUnopenedMessages(Campaign $campaign, int $count)
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
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
     * @param Campaign $campaign
     * @param int $count
     * @return Collection|Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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

    /**
     * @param Campaign $campaign
     * @param int $count
     * @return Collection|Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createBouncedMessages(Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'sent_at' => now(),
            'bounced_at' => now(),
        ]);
    }

    /**
     * @param Campaign $campaign
     * @param int $count
     * @return Collection|Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createPendingMessages(Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'sent_at' => null,
        ]);
    }

    /**
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return Collection|Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createClickedMessages(Campaign $campaign, int $quantity = 1, array $overrides = [])
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
}
