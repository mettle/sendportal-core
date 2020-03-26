<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Support\Arr;
use Sendportal\Base\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WorkspacesControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function can_retrieve_paginated_list_of_the_users_workspaces()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        // when
        $response = $this->get(route('api.workspaces.index'), [
            'Authorization' => 'Bearer ' . $user->api_token
        ]);

        // then
        $response->assertOk();

        $response->assertJson([
            'data' => [
                Arr::only($workspace->toArray(), ['name'])
            ]
        ]);
    }

    /** @test */
    function only_the_workspaces_for_the_current_user_are_retrieved()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $secondWorkspace = factory(Workspace::class)->create();

        // when
        $response = $this->get(route('api.workspaces.index'), [
            'Authorization' => 'Bearer ' . $user->api_token
        ]);

        // then
        $response->assertOk();

        $response->assertJson([
            'data' => [
                Arr::only($workspace->toArray(), ['name'])
            ]
        ]);

        $response->assertJsonMissing([
            'data' => [
                Arr::only($secondWorkspace->toArray(), ['name'])
            ]
        ]);
    }
}
