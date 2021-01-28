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

        $route = route('sendportal.api.segments.index');

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
        $route = route('sendportal.api.segments.store');

        $request = [
            'name' => $this->faker->colorName,
        ];

        $this->postJson($route, $request)
            ->assertStatus(201)
            ->assertJson(['data' => $request]);

        $this->assertDatabaseHas('sendportal_segments', $request);
    }

    /** @test */
    public function a_segment_can_be_updated()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.update', [
            'segment' => $segment->id,
        ]);

        $request = [
            'name' => 'newName',
        ];

        $this->putJson($route, $request)
            ->assertOk()
            ->assertJson(['data' => $request]);

        $this->assertDatabaseMissing('sendportal_segments', $segment->toArray());
        $this->assertDatabaseHas('sendportal_segments', $request);
    }

    /** @test */
    public function a_segment_can_be_deleted()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.destroy', [
            'segment' => $segment->id,
        ]);

        $this->deleteJson($route)
            ->assertStatus(204);

        $this->assertDatabaseCount('sendportal_segments', 0);
    }

    /** @test */
    public function a_segment_name_must_be_unique_for_a_workspace()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.store');

        $request = [
            'name' => $segment->name,
        ];

        $this->postJson($route, $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        self::assertEquals(1, Segment::where('name', $segment->name)->count());
    }

    /** @test */
    public function two_workspaces_can_have_the_same_name_for_a_segment()
    {
        $segment = $this->createSegment();

        $currentWorkspaceId = Sendportal::currentWorkspaceId();

        Sendportal::setCurrentWorkspaceIdResolver(function () use ($currentWorkspaceId) {
            return $currentWorkspaceId + 1;
        });

        $route = route('sendportal.api.segments.store');

        $request = [
            'name' => $segment->name,
        ];

        $this->postJson($route, $request)
            ->assertStatus(201);

        self::assertEquals(2, Segment::where('name', $segment->name)->count());
    }
}
