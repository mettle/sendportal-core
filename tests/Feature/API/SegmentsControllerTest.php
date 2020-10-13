<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;
use Tests\TestCase;

class SegmentsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function a_list_of_a_workspaces_segments_can_be_retrieved()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.index', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
        ]);

        $this->getJson($route)
            ->assertOk()
            ->assertJson([
                'data' => [
                    Arr::only($segment->toArray(), ['name'])
                ],
            ]);
    }

    /** @test */
    public function a_single_segment_can_be_retrieved()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.show', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'segment' => $segment->id,
        ]);

        $this->getJson($route)
            ->assertOk()
            ->assertJson([
                'data' => Arr::only($segment->toArray(), ['name']),
            ]);
    }

    /** @test */
    public function a_new_segment_can_be_added()
    {
        $route = route('sendportal.api.segments.store', Sendportal::currentWorkspaceId());

        $request = [
            'name' => $this->faker->colorName,
        ];

        $this->postJson($route, $request)
            ->assertStatus(201)
            ->assertJson(['data' => $request]);

        $this->assertDatabaseHas('segments', $request);
    }

    /** @test */
    public function a_segment_can_be_updated()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.update', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'segment' => $segment->id,
        ]);

        $request = [
            'name' => 'newName',
        ];

        $this->putJson($route, $request)
            ->assertOk()
            ->assertJson(['data' => $request]);

        $this->assertDatabaseMissing('segments', $segment->toArray());
        $this->assertDatabaseHas('segments', $request);
    }

    /** @test */
    public function a_segment_can_be_deleted()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.destroy', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'segment' => $segment->id,
        ]);

        $this->deleteJson($route)
            ->assertStatus(204);

        $this->assertDatabaseCount('segments', 0);
    }

    /** @test */
    public function a_segment_name_must_be_unique_for_a_workspace()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.store', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
        ]);

        $request = [
            'name' => $segment->name,
        ];

        $this->postJson($route, $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->assertEquals(1, Segment::where('name', $segment->name)->count());
    }

    /** @test */
    public function two_workspaces_can_have_the_same_name_for_a_segment()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.store', [
            'workspaceId' => Sendportal::currentWorkspaceId() + 1,
        ]);

        $request = [
            'name' => $segment->name,
        ];

        $this->postJson($route, $request)
            ->assertStatus(201);

        $this->assertEquals(2, Segment::where('name', $segment->name)->count());
    }
}
