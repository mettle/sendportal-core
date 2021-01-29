<?php

declare(strict_types=1);

namespace Tests\Feature\Subscribers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Tag;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class SubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function new_subscribers_can_be_created_by_authenticated_users()
    {
        // given
        $subscriberStoreData = [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        // when
        $response = $this->post(route('sendportal.subscribers.store'), $subscriberStoreData);

        // then
        $response->assertRedirect();

        $this->assertDatabaseHas('sendportal_subscribers', [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email' => $subscriberStoreData['email']
        ]);
    }

    /** @test */
    public function the_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        $subscriber = Subscriber::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.subscribers.edit', $subscriber->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_subscriber_is_updateable_by_an_authenticated_user()
    {
        // given
        $subscriber = Subscriber::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

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

        $this->assertDatabaseHas('sendportal_subscribers', [
            'id' => $subscriber->id,
            'email' => $subscriberUpdateData['email'],
            'first_name' => $subscriberUpdateData['first_name'],
            'last_name' => $subscriberUpdateData['last_name'],
        ]);
    }

    /** @test */
    public function the_show_view_is_accessible_by_an_authenticated_user()
    {
        // given
        $subscriber = Subscriber::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.subscribers.show', $subscriber->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function the_subscribers_index_lists_subscribers()
    {
        // given
        $subscriber = Subscriber::factory()->count(5)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        // when
        $response = $this->get(route('sendportal.subscribers.index'));

        // then
        $subscriber->each(static function (Subscriber $subscriber) use ($response) {
            $response->assertSee($subscriber->email);
            $response->assertSee("{$subscriber->first_name} {$subscriber->last_name}");
        });
    }

    /** @test */
    public function the_subscribers_index_can_be_filtered_by_tags()
    {
        // given
        $firstTag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $secondTag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $thirdTag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $firstTagSubscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $secondTagSubscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $thirdTagSubscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $firstTag->subscribers()->attach($firstTagSubscriber->id);
        $secondTag->subscribers()->attach($secondTagSubscriber->id);
        $thirdTag->subscribers()->attach($thirdTagSubscriber->id);

        // when
        $response = $this->get(route('sendportal.subscribers.index', [
            'tags' => [$firstTag->id, $secondTag->id]
        ]));

        // then
        $response->assertSee($firstTagSubscriber->email);
        $response->assertSee("{$firstTagSubscriber->first_name} {$firstTagSubscriber->last_name}");
        $response->assertSee($secondTagSubscriber->email);
        $response->assertSee("{$secondTagSubscriber->first_name} {$secondTagSubscriber->last_name}");
        $response->assertDontSee($thirdTagSubscriber->email);
        $response->assertDontSee("{$thirdTagSubscriber->first_name} {$thirdTagSubscriber->last_name}");
    }
}
