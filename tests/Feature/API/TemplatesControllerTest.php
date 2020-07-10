<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Models\Template;
use Sendportal\Base\Traits\NormalizeTags;
use Tests\TestCase;

class TemplatesControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker,
        NormalizeTags;

    /** @test */
    public function the_template_index_is_accessible_to_authorised_users()
    {
        $user = $this->createUserWithWorkspace();

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $route = route('sendportal.api.templates.index', [
            'workspaceId' => $user->currentWorkspace()->id,
            'api_token' => $user->api_token,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                Arr::only($template->toArray(), ['id', 'name', 'content'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_single_template_is_accessible_to_authorised_users()
    {
        $user = $this->createUserWithWorkspace();

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $route = route('sendportal.api.templates.show', [
            'workspaceId' => $user->currentWorkspace()->id,
            'template' => $template->id,
            'api_token' => $user->api_token,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => Arr::only($template->toArray(), ['id', 'name', 'content']),
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_template_can_be_created_by_authorised_users()
    {
        $user = $this->createUserWithWorkspace();

        $route = route('sendportal.api.templates.store', $user->currentWorkspace()->id);

        $request = [
            'name' => $this->faker->name,
            'content' => 'Hello {{ content }}',
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        $normalisedRequest = [
            'name' => $request['name'],
            'content' => $this->normalizeTags($request['content'], 'content')
        ];

        $response->assertStatus(201);
        $this->assertDatabaseHas('templates', $normalisedRequest);
        $response->assertJson(['data' => $normalisedRequest]);
    }

    /** @test */
    public function a_template_can_be_updated_by_authorised_users()
    {
        $user = $this->createUserWithWorkspace();

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $route = route('sendportal.api.templates.update', [
            'workspaceId' => $user->currentWorkspace()->id,
            'template' => $template->id,
            'api_token' => $user->api_token,
        ]);

        $request = [
            'name' => 'newName',
            'content' => 'newContent {{ content }}',
        ];

        $response = $this->put($route, $request);

        $normalisedRequest = [
            'name' => $request['name'],
            'content' => $this->normalizeTags($request['content'], 'content')
        ];

        $response->assertStatus(200);
        $this->assertDatabaseMissing('templates', $template->toArray());
        $this->assertDatabaseHas('templates', $normalisedRequest);
        $response->assertJson(['data' => $normalisedRequest]);
    }

    /** @test */
    public function a_template_can_be_deleted_by_authorised_users()
    {
        $user = $this->createUserWithWorkspace();

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $route = route('sendportal.api.templates.destroy', [
            'workspaceId' => $user->currentWorkspace()->id,
            'template' => $template->id,
            'api_token' => $user->api_token,
        ]);

        $response = $this->delete($route);

        $response->assertStatus(204);
    }
}
