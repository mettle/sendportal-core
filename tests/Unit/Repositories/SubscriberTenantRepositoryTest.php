<?php

namespace Tests\Unit\Repositories;

use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Tests\SendportalTestSupportTrait;
use Tests\TestCase;

class SubscriberTenantRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use SendportalTestSupportTrait;

    protected $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app()->make(SubscriberTenantRepositoryInterface::class);
    }

    /** @test */
    public function it_should_get_the_grow_chart_data()
    {
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        [$workspace, $_] = $this->createUserAndWorkspace();

        $data = $this->repository->getGrowthChartData($period, $workspace->id);

        $this->assertArrayHasKey('startingValue', $data);
        $this->assertArrayHasKey('runningTotal', $data);
        $this->assertArrayHasKey('unsubscribers', $data);
    }

    /** @test */
    public function it_should_get_the_total_number_of_subscribers_created_before_the_reference_period()
    {
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        [$workspace, $_] = $this->createUserAndWorkspace();

        factory(Subscriber::class, 2)->create([
            'workspace_id' => $workspace->id,
            'created_at' => $period->getStartDate()->subDay()
        ]);

        $this->assertEquals(2, $this->repository->getGrowthChartData($period, $workspace->id)['startingValue']);
    }

    /** @test */
    public function it_should_get_the_total_number_of_subscribers_in_the_reference_period_grouped_by_date()
    {
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        [$workspace, $_] = $this->createUserAndWorkspace();

        factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id,
            'created_at' => $period->getStartDate()->addDay()
        ]);
        factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id,
            'created_at' => $period->getEndDate()->subDay()
        ]);

        $ignored = factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id,
            'created_at' => $period->getEndDate()->addDay()
        ]);

        $runningTotal = $this->repository->getGrowthChartData($period, $workspace->id)['runningTotal'];

        $this->assertEquals(2, $runningTotal->count());
    }

    /** @test */
    public function it_should_get_the_total_number_of_unsubscribers_in_the_reference_period_grouped_by_date()
    {
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        [$workspace, $_] = $this->createUserAndWorkspace();

        $unsubscribed_at = $period->getStartDate()->addWeek();

        factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id,
            'created_at' => $period->getStartDate()->addDay(),
            'unsubscribed_at' => $unsubscribed_at
        ]);
        factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id,
            'created_at' => $period->getEndDate()->subDay()
        ]);

        $unsubscribers = $this->repository->getGrowthChartData($period, $workspace->id)['unsubscribers'];

        $this->assertEquals(1, $unsubscribers->count());
        $this->assertTrue($unsubscribers->has($unsubscribed_at->format('d-m-Y')));
        $this->assertEquals(1, $unsubscribers->get($unsubscribed_at->format('d-m-Y'))->total);
    }
}
