<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Facades\Sendportal;
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

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                Arr::only($segment->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_single_segment_can_be_retrieved()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.show', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'segment' => $segment->id,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => Arr::only($segment->toArray(), ['name']),
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_new_segment_can_be_added()
    {
        $route = route('sendportal.api.segments.store', Sendportal::currentWorkspaceId());

        $request = [
            'name' => $this->faker->colorName,
        ];

        $response = $this->post($route, $request);

        $response->assertStatus(201);
        $this->assertDatabaseHas('segments', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_segment_can_be_created()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.update', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'segment' => $segment->id,
        ]);

        $request = [
            'name' => 'newName',
        ];

        $response = $this->put($route, $request);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('segments', $segment->toArray());
        $this->assertDatabaseHas('segments', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_segment_can_be_deleted()
    {
        $segment = $this->createSegment();

        $route = route('sendportal.api.segments.destroy', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'segment' => $segment->id,
        ]);

        $response = $this->delete($route);

        $response->assertStatus(204);
    }
}
