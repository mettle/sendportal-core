<?php

namespace Tests\Unit\Repositories;

use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTenantRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function the_get_average_time_to_open_method_returns_the_average_time_taken_to_open_a_campaigns_message()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();
        $campaign = $this->createCampaign($team, $provider);

        // 30 seconds
        $this->createOpenedMessage($team, $campaign, 1, [
            'delivered_at' => now(),
            'opened_at' => now()->addSeconds(30),
        ]);

        // 60 seconds
        $this->createOpenedMessage($team, $campaign, 1, [
            'delivered_at' => now(),
            'opened_at' => now()->addSeconds(60),
        ]);

        $averageTimeToOpen = app()->make(CampaignTenantInterface::class)->getAverageTimeToOpen($campaign);

        // 45 seconds
        static::assertEquals('00:00:45', $averageTimeToOpen);
    }

    /** @test */
    function the_get_average_time_to_open_method_returns_na_if_there_have_been_no_opens()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();
        $campaign = $this->createCampaign($team, $provider);

        $averageTimeToOpen = app()->make(CampaignTenantInterface::class)->getAverageTimeToOpen($campaign);

        static::assertEquals('N/A', $averageTimeToOpen);
    }

    /** @test */
    function the_get_average_time_to_click_method_returns_the_average_time_taken_for_a_campaign_link_to_be_clicked_for_the_first_time()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();
        $campaign = $this->createCampaign($team, $provider);

        // 30 seconds
        $this->createClickedMessage($team, $campaign, 1, [
            'delivered_at' => now(),
            'clicked_at' => now()->addSeconds(30),
        ]);

        // 30 seconds
        $this->createClickedMessage($team, $campaign, 1, [
            'delivered_at' => now(),
            'clicked_at' => now()->addSeconds(60),
        ]);

        $averageTimeToClick = app()->make(CampaignTenantInterface::class)->getAverageTimeToClick($campaign);

        static::assertEquals('00:00:45', $averageTimeToClick);
    }

    /** @test */
    function the_average_time_to_click_attribute_returns_na_if_there_have_been_no_clicks()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();
        $campaign = $this->createCampaign($team, $provider);

        $averageTimeToClick = app()->make(CampaignTenantInterface::class)->getAverageTimeToClick($campaign);

        static::assertEquals('N/A', $averageTimeToClick);
    }

    /**
     * @return array
     */
    protected function createUserWithTeamAndProvider(): array
    {
        $user = factory(User::class)->create();
        $team = factory(Team::class)->create([
            'owner_id' => $user->id,
        ]);
        $provider = factory(Provider::class)->create([
            'team_id' => $team->id,
        ]);

        return [$team, $provider];
    }

    /**
     * @param Team $team
     * @param Provider $provider
     *
     * @return Campaign
     */
    protected function createCampaign(Team $team, Provider $provider): Campaign
    {
        return factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'team_id' => $team->id,
            'provider_id' => $provider->id,
        ]);
    }

    /**
     * @param Team $team
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return mixed
     */
    protected function createOpenedMessage(Team $team, Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'team_id' => $team->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'team_id' => $team->id,
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
     * @param Team $team
     * @param Campaign $campaign
     * @param int $count
     *
     * @return mixed
     */
    protected function createUnopenedMessage(Team $team, Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'team_id' => $team->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'team_id' => $team->id,
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    /**
     * @param Team $team
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return mixed
     */
    protected function createClickedMessage(Team $team, Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'team_id' => $team->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'team_id' => $team->id,
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
     * @param Team $team
     * @param Campaign $campaign
     * @param int $count
     *
     * @return mixed
     */
    protected function createUnclickedMessage(Team $team, Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'team_id' => $team->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'team_id' => $team->id,
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }
}
