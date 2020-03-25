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
        $user = $this->createUserWithTeam();

        $segment = $this->createSegment($user);
        $subscriber = $this->createSubscriber($user);

        $subscriber->segments()->save($segment);

        $route = route('api.subscribers.segments.index', [
            'teamId' => $user->currentTeam()->id,
            'subscriber' => $subscriber->id,
            'api_token' => $user->api_token,
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
        $user = $this->createUserWithTeam();

        $segment = $this->createSegment($user);
        $subscriber = $this->createSubscriber($user);

        $route = route('api.subscribers.segments.store', [
            'teamId' => $user->currentTeam()->id,
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'segments' => [$segment->id]
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('segment_subscriber', [
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
        $user = $this->createUserWithTeam();

        $subscriber = $this->createSubscriber($user);
        $oldSegment = $this->createSegment($user);
        $newSegment = $this->createSegment($user);

        $subscriber->segments()->save($oldSegment);

        $route = route('api.subscribers.segments.update', [
            'teamId' => $user->currentTeam()->id,
            'subscriber' => $subscriber->id,
            'api_token' => $user->api_token,
        ]);

        $request = [
            'segments' => [$newSegment->id],
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('segment_subscriber', [
            'segment_id' => $oldSegment->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $this->assertDatabaseHas('segment_subscriber', [
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
        $user = $this->createUserWithTeam();

        $segment = $this->createSegment($user);
        $subscriber = $this->createSubscriber($user);

        $subscriber->segments()->save($segment);

        $route = route('api.subscribers.segments.destroy', [
            'teamId' => $user->currentTeam()->id,
            'subscriber' => $subscriber->id,
            'api_token' => $user->api_token,
        ]);

        $request = [
            'segments' => [$segment->id],
        ];

        $response = $this->delete($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('segment_subscriber', [
            'segment_id' => $segment->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }
}
