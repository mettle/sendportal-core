<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class SubscriberTagsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

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
        $oldSegment = $this->createTag();
        $newSegment = $this->createTag();

        $subscriber->tags()->save($oldSegment);

        $route = route('sendportal.api.subscribers.tags.update', [
            'subscriber' => $subscriber->id,
        ]);

        $request = [
            'tags' => [$newSegment->id],
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('sendportal_tag_subscriber', [
            'tag_id' => $oldSegment->id,
            'subscriber_id' => $subscriber->id,
        ]);

        $this->assertDatabaseHas('sendportal_tag_subscriber', [
            'tag_id' => $newSegment->id,
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
