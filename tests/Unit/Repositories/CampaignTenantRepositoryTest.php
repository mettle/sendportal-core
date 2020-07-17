<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Tests\TestCase;

class CampaignTenantRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function the_get_average_time_to_open_method_returns_the_average_time_taken_to_open_a_campaigns_message()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();
        $campaign = $this->createCampaign($workspace, $emailService);

        // 30 seconds
        $this->createOpenedMessage($workspace, $campaign, 1, [
            'delivered_at' => now(),
            'opened_at' => now()->addSeconds(30),
        ]);

        // 60 seconds
        $this->createOpenedMessage($workspace, $campaign, 1, [
            'delivered_at' => now(),
            'opened_at' => now()->addSeconds(60),
        ]);

        $averageTimeToOpen = $this->app->make(CampaignTenantRepositoryInterface::class)->getAverageTimeToOpen($campaign);

        // 45 seconds
        static::assertEquals('00:00:45', $averageTimeToOpen);
    }

    /** @test */
    function the_get_average_time_to_open_method_returns_na_if_there_have_been_no_opens()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();
        $campaign = $this->createCampaign($workspace, $emailService);

        $averageTimeToOpen = app()->make(CampaignTenantRepositoryInterface::class)->getAverageTimeToOpen($campaign);

        static::assertEquals('N/A', $averageTimeToOpen);
    }

    /** @test */
    function the_get_average_time_to_click_method_returns_the_average_time_taken_for_a_campaign_link_to_be_clicked_for_the_first_time()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();
        $campaign = $this->createCampaign($workspace, $emailService);

        // 30 seconds
        $this->createClickedMessage($workspace, $campaign, 1, [
            'delivered_at' => now(),
            'clicked_at' => now()->addSeconds(30),
        ]);

        // 30 seconds
        $this->createClickedMessage($workspace, $campaign, 1, [
            'delivered_at' => now(),
            'clicked_at' => now()->addSeconds(60),
        ]);

        $averageTimeToClick = app()->make(CampaignTenantRepositoryInterface::class)->getAverageTimeToClick($campaign);

        static::assertEquals('00:00:45', $averageTimeToClick);
    }

    /** @test */
    function the_average_time_to_click_attribute_returns_na_if_there_have_been_no_clicks()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();
        $campaign = $this->createCampaign($workspace, $emailService);

        $averageTimeToClick = app()->make(CampaignTenantRepositoryInterface::class)->getAverageTimeToClick($campaign);

        static::assertEquals('N/A', $averageTimeToClick);
    }

    /** @test */
    function the_cancel_campaign_method_sets_the_campaign_status_to_cancelled()
    {
        $campaign = factory(Campaign::class)->state('queued')->create();

        static::assertEquals(CampaignStatus::STATUS_QUEUED, $campaign->status_id);
        $success = app(CampaignTenantRepositoryInterface::class)->cancelCampaign($campaign);

        static::assertTrue($success);
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->fresh()->status_id);
    }

    /**
     * @return array
     */
    protected function createUserWithWorkspaceAndEmailService(): array
    {
        $user = factory(User::class)->create();
        $workspace = factory(Workspace::class)->create([
            'owner_id' => $user->id,
        ]);
        $emailService = factory(EmailService::class)->create([
            'workspace_id' => $workspace->id,
        ]);

        return [$workspace, $emailService];
    }

    /**
     * @param Workspace $workspace
     * @param EmailService $emailService
     *
     * @return Campaign
     */
    protected function createCampaign(Workspace $workspace, EmailService $emailService): Campaign
    {
        return factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => $workspace->id,
            'email_service_id' => $emailService->id,
        ]);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return mixed
     */
    protected function createOpenedMessage(Workspace $workspace, Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
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
    protected function createUnopenedMessage(Workspace $workspace, Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
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
    protected function createClickedMessage(Workspace $workspace, Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
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
    protected function createUnclickedMessage(Workspace $workspace, Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }
}
