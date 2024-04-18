<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class TagSubscribersControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function the_index_gets_tag_subscribers()
    {
        $tag = $this->createTag();
        $subscriber = $this->createSubscriber();

        $tag->subscribers()->save($subscriber);

        $route = route('sendportal.api.tags.subscribers.index', [
            'tag' => $tag->id,
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
    public function a_subscriber_can_be_added_to_a_tag()
    {
        $tag = $this->createTag();
        $subscriber = $this->createsubscriber();

        $route = route('sendportal.api.tags.subscribers.store', [
            'tag' => $tag->id,
        ]);

        $request = [
            'subscribers' => [$subscriber->id]
        ];

        $response = $this->post($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sendportal_tag_subscriber', [
            'tag_id' => $tag->id,
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
        $tag = $this->createTag();
        $existingSubscriber = $this->createSubscriber();

        $tag->subscribers()->attach($existingSubscriber);

        $newSubscriber = $this->createSubscriber();

        $route = route('sendportal.api.tags.subscribers.store', [
            'tag' => $tag->id,
        ]);

        $data = [
            'subscribers' => [$existingSubscriber->id, $newSubscriber->id]
        ];

        $this->post($route, $data);

        self::assertCount(2, $tag->refresh()->subscribers);
    }

    /** @test */
    public function a_tags_subscribers_can_be_synced()
    {
        $tag = $this->createTag();
        $oldSubscriber = $this->createSubscriber();
        $newSubscriber = $this->createSubscriber();

        $tag->subscribers()->save($oldSubscriber);

        $route = route('sendportal.api.tags.subscribers.update', [
            'tag' => $tag->id,
        ]);

        $request = [
            'subscribers' => [$newSubscriber->id],
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_tag_subscriber', [
            'tag_id' => $tag->id,
            'subscriber_id' => $oldSubscriber->id,
        ]);

        $this->assertDatabaseHas('sendportal_tag_subscriber', [
            'tag_id' => $tag->id,
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
    public function a_tag_can_be_deleted()
    {
        $tag = $this->createTag();
        $subscriber = $this->createSubscriber();

        $tag->subscribers()->save($subscriber);

        $route = route('sendportal.api.tags.subscribers.destroy', [
            'tag' => $tag->id,
        ]);

        $request = [
            'subscribers' => [$subscriber->id],
        ];

        $response = $this->delete($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_tag_subscriber', [
            'tag_id' => $tag->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }
}
