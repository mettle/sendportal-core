<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class SegmentSubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function the_index_gets_segment_subscribers()
    {
        $segment = $this->createSegment();
        $subscriber = $this->createSubscriber();

        $segment->subscribers()->save($subscriber);

        $route = route('sendportal.api.segments.subscribers.index', [
            'segment' => $segment->id,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_subscriber_can_be_added_to_a_segment()
    {
        $segment = $this->createsegment();
        $subscriber = $this->createsubscriber();

        $route = route('sendportal.api.segments.subscribers.store', [
            'segment' => $segment->id,
        ]);

        $request = [
            'subscribers' => [$subscriber->id]
        ];

        $response = $this->post($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sendportal_segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function the_store_endpoint_is_idempotent()
    {
        $segment = $this->createSegment();
        $existingSubscriber = $this->createSubscriber();

        $segment->subscribers()->attach($existingSubscriber);

        $newSubscriber = $this->createSubscriber();

        $route = route('sendportal.api.segments.subscribers.store', [
            'segment' => $segment->id,
        ]);

        $data = [
            'subscribers' => [$existingSubscriber->id, $newSubscriber->id]
        ];

        $this->post($route, $data);

        self::assertCount(2, $segment->refresh()->subscribers);
    }

    /** @test */
    public function a_segments_subscribers_can_be_synced()
    {
        $segment = $this->createSegment();
        $oldSubscriber = $this->createSubscriber();
        $newSubscriber = $this->createSubscriber();

        $segment->subscribers()->save($oldSubscriber);

        $route = route('sendportal.api.segments.subscribers.update', [
            'segment' => $segment->id,
        ]);

        $request = [
            'subscribers' => [$newSubscriber->id],
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $oldSubscriber->id,
        ]);

        $this->assertDatabaseHas('sendportal_segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $newSubscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($newSubscriber->toArray(), ['first_name', 'last_name', 'email'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_segment_can_be_deleted()
    {
        $segment = $this->createSegment();
        $subscriber = $this->createSubscriber();

        $segment->subscribers()->save($subscriber);

        $route = route('sendportal.api.segments.subscribers.destroy', [
            'segment' => $segment->id,
        ]);

        $request = [
            'subscribers' => [$subscriber->id],
        ];

        $response = $this->delete($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }
}
