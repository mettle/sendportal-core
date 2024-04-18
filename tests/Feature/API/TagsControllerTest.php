<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Tag;
use Tests\TestCase;

class TagsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function a_list_of_a_workspaces_tags_can_be_retrieved()
    {
        $tag = $this->createTag();

        $route = route('sendportal.api.tags.index');

        $this->getJson($route)
            ->assertOk()
            ->assertJson([
                'data' => [
                    Arr::only($tag->toArray(), ['name'])
                ],
            ]);
    }

    /** @test */
    public function a_single_tag_can_be_retrieved()
    {
        $tag = $this->createTag();

        $route = route('sendportal.api.tags.show', [
            'tag' => $tag->id,
        ]);

        $this->getJson($route)
            ->assertOk()
            ->assertJson([
                'data' => Arr::only($tag->toArray(), ['name']),
            ]);
    }

    /** @test */
    public function a_new_tag_can_be_added()
    {
        $route = route('sendportal.api.tags.store');

        $request = [
            'name' => $this->faker->colorName(),
        ];

        $this->postJson($route, $request)
            ->assertStatus(201)
            ->assertJson(['data' => $request]);

        $this->assertDatabaseHas('sendportal_tags', $request);
    }

    /** @test */
    public function a_tag_can_be_updated()
    {
        $tag = $this->createTag();

        $route = route('sendportal.api.tags.update', [
            'tag' => $tag->id,
        ]);

        $request = [
            'name' => 'newName',
        ];

        $this->putJson($route, $request)
            ->assertOk()
            ->assertJson(['data' => $request]);

        $this->assertDatabaseMissing('sendportal_tags', $tag->toArray());
        $this->assertDatabaseHas('sendportal_tags', $request);
    }

    /** @test */
    public function a_tag_can_be_deleted()
    {
        $tag = $this->createTag();

        $route = route('sendportal.api.tags.destroy', [
            'tag' => $tag->id,
        ]);

        $this->deleteJson($route)
            ->assertStatus(204);

        $this->assertDatabaseCount('sendportal_tags', 0);
    }

    /** @test */
    public function a_tag_name_must_be_unique_for_a_workspace()
    {
        $tag = $this->createTag();

        $route = route('sendportal.api.tags.store');

        $request = [
            'name' => $tag->name,
        ];

        $this->postJson($route, $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        self::assertEquals(1, Tag::where('name', $tag->name)->count());
    }

    /** @test */
    public function two_workspaces_can_have_the_same_name_for_a_tag()
    {
        $tag = $this->createTag();

        $currentWorkspaceId = Sendportal::currentWorkspaceId();

        Sendportal::setCurrentWorkspaceIdResolver(function () use ($currentWorkspaceId) {
            return $currentWorkspaceId + 1;
        });

        $route = route('sendportal.api.tags.store');

        $request = [
            'name' => $tag->name,
        ];

        $this->postJson($route, $request)
            ->assertStatus(201);

        self::assertEquals(2, Tag::where('name', $tag->name)->count());
    }
}
