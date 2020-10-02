<?php

declare(strict_types=1);

namespace Tests\Feature\Subscribers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class SubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function the_index_of_subscribers_is_accessible_to_authenticated_users()
    {
        factory(Subscriber::class, 3)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.subscribers.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    function the_subscriber_create_form_is_accessilbe_to_authenticated_users()
    {
        // when
        $response = $this->get(route('sendportal.subscribers.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    function new_subscribers_can_be_created_by_authenticated_users()
    {
        $subscriberStoreData = [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        // when
        $response = $this
            ->post(route('sendportal.subscribers.store'), $subscriberStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('subscribers', [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email' => $subscriberStoreData['email']
        ]);
    }

    /** @test */
    function the_edit_view_is_accessible_by_authenticated_users()
    {
        $subscriber = factory(Subscriber::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.subscribers.edit', $subscriber->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function a_subscriber_is_updateable_by_an_authenticated_user()
    {
        $subscriber = factory(Subscriber::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $subscriberUpdateData = [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        // when
        $response = $this
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
        $subscriber = factory(Subscriber::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this
            ->get(route('sendportal.subscribers.show', $subscriber->id));

        // then
        $response->assertOk();
    }
}
