<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Facades\Sendportal;
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
        $subscriber = $this->createSubscriber();

        // when
        $route = route('sendportal.api.subscribers.index');

        $response = $this->get($route);

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
        $subscriber = $this->createSubscriber();

        // when
        $route = route('sendportal.api.subscribers.show', [
            'subscriber' => $subscriber->id,
        ]);

        $response = $this->get($route);

        // then
        $response->assertStatus(200);

        // then
        $response->assertJson([
            'data' => Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email']),
        ]);
    }

    /** @test */
    public function a_subscriber_can_be_created_by_authorised_users()
    {
        // when
        $route = route('sendportal.api.subscribers.store');

        $request = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
        ];

        $response = $this->post($route, $request);

        // then
        $response->assertStatus(201);
        $this->assertDatabaseHas('sendportal_subscribers', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_subscriber_can_be_updated_by_authorised_users()
    {
        // given
        $subscriber = $this->createSubscriber();

        // when
        $route = route('sendportal.api.subscribers.update', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'email' => 'newEmail@example.com',
        ];

        $response = $this->put($route, $request);

        // then
        $response->assertStatus(200);
        $this->assertDatabaseMissing('sendportal_subscribers', $subscriber->toArray());
        $this->assertDatabaseHas('sendportal_subscribers', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_subscriber_can_be_deleted_by_authorised_users()
    {
        // given
        $subscriber = $this->createSubscriber();

        // when
        $route = route('sendportal.api.subscribers.destroy', [
            'subscriber' => $subscriber->id,
        ]);

        $response = $this->delete($route);

        // then
        $response->assertStatus(204);
        $this->assertDatabaseMissing('sendportal_subscribers', ['id' => $subscriber->id]);
    }

    /** @test */
    public function a_subscriber_in_a_segment_can_be_deleted()
    {
        // given
        $subscriber = $this->createSubscriber();
        $segment = Segment::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);
        $subscriber->segments()->attach($segment->id);

        // when
        $response = $this->delete(route('sendportal.api.subscribers.destroy', [
            'subscriber' => $subscriber->id,
        ]));

        // then
        $response->assertStatus(204);
        $this->assertDatabaseMissing('sendportal_subscribers', ['id' => $subscriber->id]);
        $this->assertDatabaseMissing('sendportal_segment_subscriber', [
            'subscriber_id' => $subscriber->id
        ]);
    }

    /** @test */
    public function the_store_endpoint_can_update_subscriber_based_on_email_address()
    {
        // given
        $subscriber = $this->createSubscriber();

        // when
        $route = route('sendportal.api.subscribers.store');

        $updateData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $subscriber->email,
        ];

        $response = $this->post($route, $updateData);

        // then
        $response->assertStatus(200);

        $this->assertDatabaseHas('sendportal_subscribers', array_merge($updateData, ['id' => $subscriber->id]));
        $this->assertDatabaseCount('sendportal_subscribers', 1);

        $response->assertJson(['data' => $updateData]);
    }

    /** @test */
    public function the_store_endpoint_allows_segments_to_be_added_with_the_subscriber()
    {
        // given
        $segment = $this->createSegment();

        // when
        $route = route('sendportal.api.subscribers.store');

        $request = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'segments' => [$segment->id]
        ];

        $response = $this->post($route, $request);

        // then
        $response->assertStatus(201);

        $this->assertDatabaseHas('sendportal_subscribers', ['email' => $request['email']]);

        $subscriber = Subscriber::with('segments')->where('email', $request['email'])->first();

        self::assertContains($segment->id, $subscriber->segments->pluck('id'));
    }

    /** @test */
    public function the_store_endpoint_allows_subscriber_segments_to_be_updated()
    {
        // given
        $segment1 = $this->createSegment();
        $segment2 = $this->createSegment();

        $subscriber = $this->createSubscriber();
        $subscriber->segments()->save($segment1);

        // when
        $route = route('sendportal.api.subscribers.store');

        $request = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $subscriber->email,
            'segments' => [$segment2->id]
        ];

        $response = $this->post($route, $request);

        // then
        $response->assertStatus(200);

        $subscriber = $subscriber->fresh();
        $subscriber->load('segments');

        self::assertContains($segment2->id, $subscriber->segments->pluck('id'));
        self::assertNotContains($segment1->id, $subscriber->segments->pluck('id'));
    }
}
