<?php

namespace Tests\Unit\Repositories;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Sendportal\Base\Repositories\Messages\MySqlMessageTenantRepository;
use Sendportal\Base\Repositories\Messages\PostgresMessageTenantRepository;
use Tests\SendportalTestSupportTrait;
use Tests\TestCase;

class MessageTenantRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var MySqlMessageTenantRepository|PostgresMessageTenantRepository
     */
    protected $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app()->make(MessageTenantRepositoryInterface::class);
    }

    /** @test */
    function it_should_not_count_messages_that_have_not_been_opened_yet()
    {
        $workspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id
        ]);

        factory(Message::class, 2)->create([
            'workspace_id' => $workspace->id,
            'source_id' => $campaign->id,
        ]);

        $data = $this->repository->countUniqueOpensPerPeriod($workspace->id, get_class($campaign), $campaign->id, CarbonInterval::day()->totalSeconds);

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertTrue($data->isEmpty());
    }

    /** @test */
    function it_should_count_messages_that_have_been_opened_grouped_by_day_period()
    {
        $opened_at = CarbonImmutable::create(2020, 05, 9, 20);

        $workspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id
        ]);

        // 20 - 21 - 22 - 23
        foreach (range(0, 3) as $i) {
            factory(Message::class)->create([
                'workspace_id' => $workspace->id,
                'source_id' => $campaign->id,
                'opened_at' => $opened_at->addHours($i)
            ]);
        }

        // 24
        $next_opened_at = $opened_at->addHours(4);

        factory(Message::class)->create([
            'workspace_id' => $workspace->id,
            'source_id' => $campaign->id,
            'opened_at' => $next_opened_at
        ]);

        $data = $this->repository->countUniqueOpensPerPeriod($workspace->id, get_class($campaign), $campaign->id, CarbonInterval::day()->totalSeconds);

        $this->assertEquals(2, $data->count());

        $this->assertEquals(4, $data->first()->open_count);
        $this->assertEquals($opened_at->toDateTimeString(), $data->first()->opened_at);
        $this->assertEquals($opened_at->startOfDay()->toDateTimeString(), $data->first()->period_start);

        $this->assertEquals(1, $data->last()->open_count);
        $this->assertEquals($next_opened_at->toDateTimeString(), $data->last()->opened_at);
        $this->assertEquals($next_opened_at->startOfDay()->toDateTimeString(), $data->last()->period_start);
    }

    /** @test */
    function it_should_count_messages_that_have_been_opened_grouped_by_two_hours_period()
    {
        $opened_at = CarbonImmutable::create(2020, 05, 9, 20);

        $workspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id
        ]);

        // 20 - 21 - 22 - 23
        foreach (range(0, 3) as $i) {
            factory(Message::class)->create([
                'workspace_id' => $workspace->id,
                'source_id' => $campaign->id,
                'opened_at' => $opened_at->addHours($i)
            ]);
        }

        // 24
        $next_opened_at = $opened_at->addHours(4);

        factory(Message::class)->create([
            'workspace_id' => $workspace->id,
            'source_id' => $campaign->id,
            'opened_at' => $next_opened_at
        ]);

        $data = $this->repository->countUniqueOpensPerPeriod($workspace->id, get_class($campaign), $campaign->id, CarbonInterval::hours(2)->totalSeconds);

        $this->assertEquals(3, $data->count());

        // 20
        $this->assertEquals(2, $data[0]->open_count);
        $this->assertEquals($opened_at->toDateTimeString(), $data[0]->opened_at);
        $this->assertEquals($opened_at->toDateTimeString(), $data[0]->period_start);

        // 22
        $this->assertEquals(2, $data[0]->open_count);
        $this->assertEquals($opened_at->addHours(2)->toDateTimeString(), $data[1]->opened_at);
        $this->assertEquals($opened_at->addHours(2)->toDateTimeString(), $data[1]->period_start);

        // 24
        $this->assertEquals(1, $data[2]->open_count);
        $this->assertEquals($next_opened_at->toDateTimeString(), $data[2]->opened_at);
        $this->assertEquals($next_opened_at->startOfDay()->toDateTimeString(), $data[2]->period_start);
    }

    /** @test */
    function it_should_count_messages_that_have_been_opened_grouped_by_hour_period()
    {
        $opened_at = CarbonImmutable::create(2020, 05, 9, 20);

        $workspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id
        ]);

        // 20 - 21 - 22 - 23
        foreach (range(0, 3) as $i) {
            factory(Message::class)->create([
                'workspace_id' => $workspace->id,
                'source_id' => $campaign->id,
                'opened_at' => $opened_at->addHours($i)
            ]);
        }

        // 24
        $next_opened_at = $opened_at->addHours(4);

        factory(Message::class)->create([
            'workspace_id' => $workspace->id,
            'source_id' => $campaign->id,
            'opened_at' => $next_opened_at
        ]);

        $data = $this->repository->countUniqueOpensPerPeriod($workspace->id, get_class($campaign), $campaign->id, CarbonInterval::hour()->totalSeconds);

        $this->assertEquals(5, $data->count());

        foreach (range(0, 3) as $i) {
            $this->assertEquals(1, $data[$i]->open_count);
            $this->assertEquals($opened_at->addHours($i)->toDateTimeString(), $data[$i]->period_start);
        }

        $this->assertEquals(1, $data->last()->open_count);
        $this->assertEquals($next_opened_at->toDateTimeString(), $data->last()->opened_at);
        $this->assertEquals($next_opened_at->startOfDay()->toDateTimeString(), $data->last()->period_start);
    }
}
