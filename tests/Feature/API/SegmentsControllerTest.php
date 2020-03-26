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
    public function a_list_of_a_teams_segments_can_be_retreived()
    {
        $user = $this->createUserWithTeam();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.index', [
            'teamId' => $user->currentTeam()->id,
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
    public function a_single_segment_can_be_retreived()
    {
        $user = $this->createUserWithTeam();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.show', [
            'teamId' => $user->currentTeam()->id,
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
        $user = $this->createUserWithTeam();

        $route = route('sendportal.api.segments.store', $user->currentTeam()->id);

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
        $user = $this->createUserWithTeam();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.update', [
            'teamId' => $user->currentTeam()->id,
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
        $user = $this->createUserWithTeam();

        $segment = $this->createSegment($user);

        $route = route('sendportal.api.segments.destroy', [
            'teamId' => $user->currentTeam()->id,
            'segment' => $segment->id,
            'api_token' => $user->api_token,
        ]);

        $response = $this->delete($route);

        $response->assertStatus(204);
    }
}
