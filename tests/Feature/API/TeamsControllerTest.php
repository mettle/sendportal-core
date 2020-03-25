<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Sendportal\Base\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeamsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function can_retrieve_paginated_list_of_the_users_teams()
    {
        // given
        [$team, $user] = $this->createUserAndTeam();

        // when
        $response = $this->get(route('api.teams.index'), [
            'Authorization' => 'Bearer ' . $user->api_token
        ]);

        // then
        $response->assertOk();

        $response->assertJson([
            'data' => [
                array_only($team->toArray(), ['name'])
            ]
        ]);
    }

    /** @test */
    function only_the_teams_for_the_current_user_are_retrieved()
    {
        // given
        [$team, $user] = $this->createUserAndTeam();

        $secondTeam = factory(Team::class)->create();

        // when
        $response = $this->get(route('api.teams.index'), [
            'Authorization' => 'Bearer ' . $user->api_token
        ]);

        // then
        $response->assertOk();

        $response->assertJson([
            'data' => [
                array_only($team->toArray(), ['name'])
            ]
        ]);

        $response->assertJsonMissing([
            'data' => [
                array_only($secondTeam->toArray(), ['name'])
            ]
        ]);
    }
}
