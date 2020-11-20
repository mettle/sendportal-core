<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class SubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function the_subscribers_index_is_accessible_to_authorised_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $subscriber = $this->createSubscriber($user);

        // when
        $response = $this->get(route('sendportal.api.subscribers.index', [
            'workspaceId' => $user->currentWorkspace()->id,
            'api_token' => $user->api_token,
        ]));

        // then
        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email'])
            ],
        ]);
    }

    /** @test */
    public function a_single_subscriber_is_accessible_to_authorised_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $subscriber = $this->createSubscriber($user);

        // when
        $response = $this->get(route('sendportal.api.subscribers.show', [
            'workspaceId' => $user->currentWorkspace()->id,
            'subscriber' => $subscriber->id,
            'api_token' => $user->api_token,
        ]));

        // then
        $response->assertStatus(200);

        $response->assertJson([
            'data' => Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email']),
        ]);
    }

    /** @test */
    public function a_subscriber_can_be_created_by_authorised_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        // when
        $route = route('sendportal.api.subscribers.store', $user->currentWorkspace()->id);

        $request = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        // then
        $response->assertStatus(201);
        $this->assertDatabaseHas('subscribers', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_subscriber_can_be_updated_by_authorised_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $subscriber = $this->createSubscriber($user);

        // when
        $route = route('sendportal.api.subscribers.update', [
            'workspaceId' => $user->currentWorkspace()->id,
            'subscriber' => $subscriber->id,
            'api_token' => $user->api_token,
        ]);

        $request = [
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'email' => 'newEmail@example.com',
        ];

        $response = $this->put($route, $request);

        // then
        $response->assertStatus(200);
        $this->assertDatabaseMissing('subscribers', $subscriber->toArray());
        $this->assertDatabaseHas('subscribers', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_subscriber_can_be_deleted_by_authorised_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $subscriber = $this->createSubscriber($user);

        // when
        $response = $this->delete(route('sendportal.api.subscribers.destroy', [
            'workspaceId' => $user->currentWorkspace()->id,
            'subscriber' => $subscriber->id,
            'api_token' => $user->api_token,
        ]));

        // then
        $response->assertStatus(204);
    }

    /** @test */
    public function a_subscriber_in_a_segment_can_be_deleted()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $subscriber = $this->createSubscriber($user);
        $segment = factory(Segment::class)->create(['workspace_id' => $user->currentWorkspace()->id]);
        $subscriber->segments()->attach($segment->id);

        // when
        $this->withoutExceptionHandling();
        $response = $this->delete(route('sendportal.api.subscribers.destroy', [
            'workspaceId' => $user->currentWorkspace()->id,
            'subscriber' => $subscriber->id,
            'api_token' => $user->api_token,
        ]));

        // then
        $response->assertStatus(204);
        $this->assertDatabaseMissing('subscribers', ['id' => $subscriber->id]);
        $this->assertDatabaseMissing('segment_subscriber', [
            'subscriber_id' => $subscriber->id
        ]);
    }

    /** @test */
    public function the_store_endpoint_can_update_subscriber_based_on_email_address()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $subscriber = $this->createSubscriber($user);

        // when
        $route = route('sendportal.api.subscribers.store', $user->currentWorkspace()->id);

        $updateData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $subscriber->email,
        ];

        $response = $this->post($route, array_merge($updateData, ['api_token' => $user->api_token]));

        // then
        $response->assertStatus(200);

        $this->assertDatabaseHas('subscribers', array_merge($updateData, ['id' => $subscriber->id]));
        $this->assertDatabaseCount('subscribers', 1);

        $response->assertJson(['data' => $updateData]);
    }

    /** @test */
    public function the_store_endpoint_allows_segments_to_be_added_with_the_subscriber()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);

        // when
        $route = route('sendportal.api.subscribers.store', $user->currentWorkspace()->id);

        $request = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'segments' => [$segment->id]
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        // then
        $response->assertStatus(201);

        $this->assertDatabaseHas('subscribers', ['email' => $request['email']]);

        $subscriber = Subscriber::with('segments')->where('email', $request['email'])->first();

        self::assertContains($segment->id, $subscriber->segments->pluck('id'));
    }

    /** @test */
    public function the_store_endpoint_allows_subscriber_segments_to_be_updated()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $segment1 = $this->createSegment($user);
        $segment2 = $this->createSegment($user);

        $subscriber = $this->createSubscriber($user);
        $subscriber->segments()->save($segment1);

        // when
        $route = route('sendportal.api.subscribers.store', $user->currentWorkspace()->id);

        $request = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $subscriber->email,
            'segments' => [$segment2->id]
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        // then
        $response->assertStatus(200);

        $subscriber = $subscriber->fresh();
        $subscriber->load('segments');

        self::assertContains($segment2->id, $subscriber->segments->pluck('id'));
        self::assertNotContains($segment1->id, $subscriber->segments->pluck('id'));
    }
}
