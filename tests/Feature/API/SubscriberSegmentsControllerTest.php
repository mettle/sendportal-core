<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class SubscriberSegmentsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function can_retrieve_a_list_of_a_subscribers_segments()
    {
        $segment = $this->createSegment();
        $subscriber = $this->createSubscriber();

        $subscriber->segments()->save($segment);

        $route = route('sendportal.api.subscribers.segments.index', [
            'subscriber' => $subscriber->id,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                Arr::only($segment->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function can_add_new_segments_to_the_subscriber()
    {
        $segment = $this->createSegment();
        $subscriber = $this->createSubscriber();

        $route = route('sendportal.api.subscribers.segments.store', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'segments' => [$segment->id]
        ];

        $response = $this->post($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sendportal_segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($segment->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function can_update_the_segments_associated_with_the_subscriber()
    {
        $subscriber = $this->createSubscriber();
        $oldSegment = $this->createSegment();
        $newSegment = $this->createSegment();

        $subscriber->segments()->save($oldSegment);

        $route = route('sendportal.api.subscribers.segments.update', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'segments' => [$newSegment->id],
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_segment_subscriber', [
            'segment_id' => $oldSegment->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $this->assertDatabaseHas('sendportal_segment_subscriber', [
            'segment_id' => $newSegment->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($newSegment->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function can_remove_segments_from_the_subscriber()
    {
        $segment = $this->createSegment();
        $subscriber = $this->createSubscriber();

        $subscriber->segments()->save($segment);

        $route = route('sendportal.api.subscribers.segments.destroy', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'segments' => [$segment->id],
        ];

        $response = $this->delete($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }
}
