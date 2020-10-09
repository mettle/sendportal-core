<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;
use Tests\TestCase;

class SubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function the_subscribers_index_is_accessible_to_authorised_users()
    {
        $subscriber = $this->createSubscriber();

        $route = route('sendportal.api.subscribers.index', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
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
    public function a_single_subscriber_is_accessible_to_authorised_users()
    {
        $subscriber = $this->createSubscriber();

        $route = route('sendportal.api.subscribers.show', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'subscriber' => $subscriber->id,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => Arr::only($subscriber->toArray(), ['first_name', 'last_name', 'email']),
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_subscriber_can_be_created_by_authorised_users()
    {
        $route = route('sendportal.api.subscribers.store', Sendportal::currentWorkspaceId());

        $request = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
        ];

        $response = $this->post($route, $request);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subscribers', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_subscriber_can_be_updated_by_authorised_users()
    {
        $subscriber = $this->createSubscriber();

        $route = route('sendportal.api.subscribers.update', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'email' => 'newEmail@example.com',
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('subscribers', $subscriber->toArray());
        $this->assertDatabaseHas('subscribers', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_subscriber_can_be_deleted_by_authorised_users()
    {
        $subscriber = $this->createSubscriber();

        $route = route('sendportal.api.subscribers.destroy', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'subscriber' => $subscriber->id,
        ]);

        $response = $this->delete($route);

        $response->assertStatus(204);
    }

    /** @test */
    public function a_subscriber_in_a_segment_can_be_deleted()
    {
        $subscriber = $this->createSubscriber();
        $segment = factory(Segment::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);
        $subscriber->segments()->attach($segment->id);

        // when
        $this->withoutExceptionHandling();
        $response = $this->delete(route('sendportal.api.subscribers.destroy', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'subscriber' => $subscriber->id,
        ]));

        // then
        $response->assertStatus(204);
        $this->assertDatabaseMissing('subscribers', ['id' => $subscriber->id]);
        $this->assertDatabaseMissing('segment_subscriber', [
            'subscriber_id' => $subscriber->id
        ]);
    }
}
