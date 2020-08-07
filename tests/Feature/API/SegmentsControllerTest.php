<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class SegmentsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function a_list_of_a_workspaces_segments_can_be_retrieved()
    {
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.index', [
            'workspaceId' => $user->currentWorkspace()->id,
            'api_token' => $user->api_token,
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
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.show', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
            'api_token' => $user->api_token,
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
        $user = $this->createUserWithWorkspace();

        $route = route('sendportal.api.segments.store', $user->currentWorkspace()->id);

        $request = [
            'name' => $this->faker->colorName,
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('segments', $request);
        $response->assertJson(['data' => $request]);
    }

    /** @test */
    public function a_segment_can_be_created()
    {
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.update', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
            'api_token' => $user->api_token,
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
        $user = $this->createUserWithWorkspace();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.destroy', [
            'workspaceId' => $user->currentWorkspace()->id,
            'segment' => $segment->id,
            'api_token' => $user->api_token,
        ]);

        $response = $this->delete($route);

        $response->assertStatus(204);
    }
}
