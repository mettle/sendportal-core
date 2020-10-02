<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
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
        $template = factory(Template::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.index', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
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
        $template = factory(Template::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.show', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'template' => $template->id,
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
        $route = route('sendportal.api.templates.store', Sendportal::currentWorkspaceId());

        $request = [
            'name' => $this->faker->name,
            'content' => 'Hello {{ content }}',
        ];

        $response = $this->post($route, $request);

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
        $template = factory(Template::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.update', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'template' => $template->id,
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
        $template = factory(Template::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.destroy', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'template' => $template->id,
        ]);

        $response = $this->delete($route);

        $response->assertStatus(204);
    }

    /** @test */
    function a_template_cannot_be_deleted_by_authorised_users_if_it_is_used()
    {
        $template = factory(Template::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        factory(Campaign::class)->create([
            'template_id' => $template->id
        ]);

        $route = route('sendportal.api.templates.destroy', [
            'workspaceId' => Sendportal::currentWorkspaceId(),
            'template' => $template->id,
        ]);

        $response = $this->deleteJson($route);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['template']);
    }
}
