<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class SubscriberTagsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function can_retrieve_a_list_of_a_subscribers_tags()
    {
        $tag = $this->createTag();
        $subscriber = $this->createSubscriber();

        $subscriber->tags()->save($tag);

        $route = route('sendportal.api.subscribers.tags.index', [
            'subscriber' => $subscriber->id,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                Arr::only($tag->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function can_add_new_tags_to_the_subscriber()
    {
        $tag = $this->createTag();
        $subscriber = $this->createSubscriber();

        $route = route('sendportal.api.subscribers.tags.store', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'tags' => [$tag->id]
        ];

        $response = $this->post($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sendportal_tag_subscriber', [
            'tag_id' => $tag->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($tag->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function can_update_the_tags_associated_with_the_subscriber()
    {
        $subscriber = $this->createSubscriber();
        $oldTag = $this->createTag();
        $newTag = $this->createTag();

        $subscriber->tags()->save($oldTag);

        $route = route('sendportal.api.subscribers.tags.update', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'tags' => [$newTag->id],
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_tag_subscriber', [
            'tag_id' => $oldTag->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $this->assertDatabaseHas('sendportal_tag_subscriber', [
            'tag_id' => $newTag->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $expected = [
            'data' => [
                Arr::only($newTag->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function can_remove_tags_from_the_subscriber()
    {
        $tag = $this->createTag();
        $subscriber = $this->createSubscriber();

        $subscriber->tags()->save($tag);

        $route = route('sendportal.api.subscribers.tags.destroy', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'tags' => [$tag->id],
        ];

        $response = $this->delete($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_tag_subscriber', [
            'tag_id' => $tag->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }
}
