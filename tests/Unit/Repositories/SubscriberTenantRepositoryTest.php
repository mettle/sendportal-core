<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
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
    function it_should_get_the_grow_chart_data()
    {
        // given
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        // when
        $data = $this->repository->getGrowthChartData($period, Sendportal::currentWorkspaceId());

        // then
        self::assertArrayHasKey('startingValue', $data);
        self::assertArrayHasKey('runningTotal', $data);
        self::assertArrayHasKey('unsubscribers', $data);
    }

    /** @test */
    function it_should_get_the_total_number_of_subscribers_created_before_the_reference_period()
    {
        // given
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        Subscriber::factory()->count(2)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'created_at' => $period->getStartDate()->subDay()
        ]);

        // when
        $data = $this->repository->getGrowthChartData($period, Sendportal::currentWorkspaceId());

        // then
        self::assertEquals(2, $data['startingValue']);
    }

    /** @test */
    function it_should_get_the_total_number_of_subscribers_in_the_reference_period_grouped_by_date()
    {
        // given
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'created_at' => $period->getStartDate()->addDay()
        ]);

        Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'created_at' => $period->getEndDate()->subDay()
        ]);

        Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'created_at' => $period->getEndDate()->addDay() // should be ignored
        ]);

        // when
        $runningTotal = $this->repository->getGrowthChartData($period, Sendportal::currentWorkspaceId())['runningTotal'];

        // then
        self::assertEquals(2, $runningTotal->count());
    }

    /** @test */
    function it_should_get_the_total_number_of_unsubscribers_in_the_reference_period_grouped_by_date()
    {
        // given
        $period = CarbonPeriod::create('2019-04-01', '2019-04-30');

        $unsubscribed_at = $period->getStartDate()->addWeek();

        Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'created_at' => $period->getStartDate()->addDay(),
            'unsubscribed_at' => $unsubscribed_at
        ]);

        Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'created_at' => $period->getEndDate()->subDay()
        ]);

        // when
        $unsubscribers = $this->repository->getGrowthChartData($period, Sendportal::currentWorkspaceId())['unsubscribers'];

        // then
        self::assertEquals(1, $unsubscribers->count());
        self::assertTrue($unsubscribers->has($unsubscribed_at->format('d-m-Y')));
        self::assertEquals(1, $unsubscribers->get($unsubscribed_at->format('d-m-Y'))->total);
    }
}
