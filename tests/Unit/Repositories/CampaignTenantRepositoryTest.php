<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
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
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        // 30 seconds
        $this->createOpenedMessage($campaign, 1, [
            'delivered_at' => now(),
            'opened_at' => now()->addSeconds(30),
        ]);

        // 60 seconds
        $this->createOpenedMessage($campaign, 1, [
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
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        $averageTimeToOpen = app()->make(CampaignTenantRepositoryInterface::class)->getAverageTimeToOpen($campaign);

        static::assertEquals('N/A', $averageTimeToOpen);
    }

    /** @test */
    function the_get_average_time_to_click_method_returns_the_average_time_taken_for_a_campaign_link_to_be_clicked_for_the_first_time()
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

        $averageTimeToClick = app()->make(CampaignTenantRepositoryInterface::class)->getAverageTimeToClick($campaign);

        static::assertEquals('00:00:45', $averageTimeToClick);
    }

    /** @test */
    function the_average_time_to_click_attribute_returns_na_if_there_have_been_no_clicks()
    {
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        $averageTimeToClick = app()->make(CampaignTenantRepositoryInterface::class)->getAverageTimeToClick($campaign);

        static::assertEquals('N/A', $averageTimeToClick);
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
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
     * @param Campaign $campaign
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
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
}
