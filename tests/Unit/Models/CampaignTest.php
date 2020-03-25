<?php

namespace Tests\Unit\Models;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_has_many_opens()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();
        $campaign = $this->createCampaign($team, $provider);

        $openedMessages = $this->createOpenedMessage($team, $campaign, 3);
        $this->createUnopenedMessage($team, $campaign, 2);

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
        [$team, $provider] = $this->createUserWithTeamAndProvider();

        $campaign = factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'team_id' => $team->id,
            'provider_id' => $provider->id,
        ]);
        $this->createOpenedMessage($team, $campaign, 3);

        static::assertEquals(3, $campaign->unique_open_count);
    }

    /** @test */
    function the_total_open_count_attribute_returns_the_total_number_of_opens_for_a_campaign()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();

        $campaign = $this->createCampaign($team, $provider);
        $this->createOpenedMessage($team, $campaign, 3, [
            'open_count' => 5
        ]);

        static::assertEquals(15, $campaign->total_open_count);
    }

    /** @test */
    function it_has_many_clicks()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();

        $campaign = $this->createCampaign($team, $provider);
        $clickedMessages = $this->createClickedMessage($team, $campaign, 3);
        $this->createUnclickedMessage($team, $campaign, 2);

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
        [$team, $provider] = $this->createUserWithTeamAndProvider();

        $campaign = $this->createCampaign($team, $provider);
        $this->createClickedMessage($team, $campaign, 3);

        static::assertEquals(3, $campaign->unique_click_count);
    }

    /** @test */
    function the_total_click_count_attribute_returns_the_total_number_of_clicks_for_a_campaign()
    {
        [$team, $provider] = $this->createUserWithTeamAndProvider();

        $campaign = $this->createCampaign($team, $provider);
        $this->createClickedMessage($team, $campaign, 3, [
            'click_count' => 5,
        ]);

        static::assertEquals(15, $campaign->total_click_count);
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
