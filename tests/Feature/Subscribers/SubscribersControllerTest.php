<?php

declare(strict_types=1);

namespace Tests\Feature\Subscribers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class SubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function the_index_of_subscribers_is_accessible_to_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        factory(Subscriber::class, 3)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.subscribers.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    function the_subscriber_create_form_is_accessilbe_to_authenticated_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        // when
        $response = $this->actingAs($user)->get(route('sendportal.subscribers.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    function new_subscribers_can_be_created_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $subscriberStoreData = [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.subscribers.store'), $subscriberStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('subscribers', [
            'workspace_id' => $workspace->id,
            'email' => $subscriberStoreData['email']
        ]);
    }

    /** @test */
    function the_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $subscriber = factory(Subscriber::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.subscribers.edit', $subscriber->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function a_subscriber_is_updateable_by_an_authenticated_user()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $subscriber = factory(Subscriber::class)->create(['workspace_id' => $workspace->id]);

        $subscriberUpdateData = [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        // when
        $response = $this->actingAs($user)
            ->put(route('sendportal.subscribers.update', $subscriber->id), $subscriberUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'email' => $subscriberUpdateData['email'],
            'first_name' => $subscriberUpdateData['first_name'],
            'last_name' => $subscriberUpdateData['last_name'],
        ]);
    }

    /** @test */
    function the_show_view_is_accessible_by_an_authenticated_user()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $subscriber = factory(Subscriber::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)
            ->get(route('sendportal.subscribers.show', $subscriber->id));

        // then
        $response->assertOk();
    }
}
