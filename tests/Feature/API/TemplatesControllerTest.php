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
        $template = Template::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.index');

        $this->getJson($route)
            ->assertOk()
            ->assertJson([
                'data' => [
                    Arr::only($template->toArray(), ['id', 'name', 'content'])
                ],
            ]);
    }

    /** @test */
    public function a_single_template_is_accessible_to_authorised_users()
    {
        $template = Template::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.show', [
            'template' => $template->id,
        ]);

        $this->getJson($route)
            ->assertOk()
            ->assertJson([
                'data' => Arr::only($template->toArray(), ['id', 'name', 'content']),
            ]);
    }

    /** @test */
    public function a_template_can_be_created()
    {
        $route = route('sendportal.api.templates.store');

        $request = [
            'name' => $this->faker->name,
            'content' => 'Hello {{ content }}',
        ];

        $normalisedRequest = [
            'name' => $request['name'],
            'content' => $this->normalizeTags($request['content'], 'content')
        ];

        $this
            ->postJson($route, $request)
            ->assertStatus(201)
            ->assertJson(['data' => $normalisedRequest]);

        $this->assertDatabaseHas('sendportal_templates', $normalisedRequest);
    }

    /** @test */
    public function a_template_can_be_updated_by_authorised_users()
    {
        $template = Template::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.update', [
            'template' => $template->id,
        ]);

        $request = [
            'name' => 'newName',
            'content' => 'newContent {{ content }}',
        ];

        $normalisedRequest = [
            'name' => $request['name'],
            'content' => $this->normalizeTags($request['content'], 'content')
        ];

        $this->putJson($route, $request)
            ->assertOk()
            ->assertJson(['data' => $normalisedRequest]);

        $this->assertDatabaseMissing('sendportal_templates', $template->toArray());
        $this->assertDatabaseHas('sendportal_templates', $normalisedRequest);
    }

    /** @test */
    public function a_template_can_be_deleted_by_authorised_users()
    {
        $template = Template::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.destroy', [
            'template' => $template->id,
        ]);

        $this->deleteJson($route)
            ->assertStatus(204);

        $this->assertDatabaseMissing('sendportal_templates', [
            'id' => $template->id
        ]);
    }

    /** @test */
    public function a_template_cannot_be_deleted_by_authorised_users_if_it_is_used()
    {
        $template = Template::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        Campaign::factory()->create([
            'template_id' => $template->id
        ]);

        $route = route('sendportal.api.templates.destroy', [
            'template' => $template->id,
        ]);

        $this->deleteJson($route)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['template']);
    }

    /** @test */
    public function a_template_name_must_be_unique_for_a_workspace()
    {
        $template = Template::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $route = route('sendportal.api.templates.store');

        $request = [
            'name' => $template->name,
            'content' => 'test'
        ];

        $this->postJson($route, $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        self::assertEquals(1, Template::where('name', $template->name)->count());
    }

    /** @test */
    public function two_workspaces_can_have_the_same_name_for_a_template()
    {
        $template = Template::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $currentWorkspaceId = Sendportal::currentWorkspaceId();

        Sendportal::currentWorkspaceIdResolver(function () use ($currentWorkspaceId) {
            return $currentWorkspaceId + 1;
        });

        $route = route('sendportal.api.templates.store');

        $request = [
            'name' => $template->name,
            'content' => 'newContent {{ content }}',
        ];

        $this->postJson($route, $request)
            ->assertStatus(201);

        self::assertEquals(2, Template::where('name', $template->name)->count());
    }
}
