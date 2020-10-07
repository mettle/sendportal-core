<?php

namespace Tests\Feature\Templates;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Template;
use Tests\TestCase;

class TemplatesControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    // Index.

    /** @test */
    public function a_guest_cannot_see_the_index()
    {
        $response = $this->get(route('sendportal.templates.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function a_logged_in_user_can_see_template_index()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $response = $this->get(route('sendportal.templates.index'));

        $response->assertOk();
    }

    /** @test */
    public function the_index_lists_existing_templates()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $response = $this->get(route('sendportal.templates.index'));

        $response->assertOk();
        $response->assertSee($template->name);
    }

    // Create.

    /** @test */
    public function a_guest_cannot_see_the_create_form()
    {
        $response = $this->get(route('sendportal.templates.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function a_logged_in_user_can_see_the_create_form()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $response = $this->get(route('sendportal.templates.create'));

        $response->assertOk();
        $response->assertSee('New Template');
        $response->assertSee('Template Name');
        $response->assertSee('Content');
    }

    // Store.

    /** @test */
    public function a_guest_cannot_store_a_new_template()
    {
        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->post(route('sendportal.templates.store'), $data);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('templates', [
            'name' => $data['name'],
            'content' => $data['content'],
        ]);
    }

    /** @test */
    public function a_logged_in_user_can_store_a_new_raw_template()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->post(route('sendportal.templates.store'), $data);

        $response->assertRedirect(route('sendportal.templates.index'));

        $this->assertDatabaseHas('templates', [
            'name' => $data['name'],
            'content' => $data['content'],
            'json' => null,
            'workspace_id' => $user->currentWorkspace()->id
        ]);
    }

    /** @test */
    public function a_logged_in_user_can_store_a_new_template_built_using_unlayer()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $json = [
            'key' => 'value'
        ];

        $data = [
            'name' => $this->faker->name,
            'html' => $this->faker->sentence,
            'json' => json_encode($json)
        ];

        $response = $this->post(route('sendportal.templates.store'), $data);

        $response->assertRedirect(route('sendportal.templates.index'));

        $this->assertDatabaseHas('templates', [
            'name' => $data['name'],
            'content' => $data['html'],
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $this->assertEquals($json, json_decode(Template::first()->json, true));
    }

    /** @test */
    public function a_template_cannot_be_created_without_a_name()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $this
            ->post(route('sendportal.templates.store'), [
                'content' => $this->faker->sentence
            ])
            ->assertSessionHasErrors('name');
    }

    /** @test */
    public function a_raw_template_cannot_be_created_without_content()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $this
            ->post(route('sendportal.templates.store'), [
                'name' => $this->faker->name,
            ])
            ->assertSessionHasErrors('content');
    }

    /** @test */
    public function an_unilayer_template_cannot_be_created_without_html()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $this
            ->post(route('sendportal.templates.store'), [
                'name' => $this->faker->name,
            ])
            ->assertSessionHasErrors('html')
            ->assertSessionHasErrors('json');
    }

    // Edit.

    /** @test */
    public function a_guest_cannot_see_the_edit_form()
    {
        $template = factory(Template::class)->create();

        $response = $this->get(route('sendportal.templates.edit', $template->id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function a_logged_in_user_can_see_the_edit_form()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $response = $this->get(route('sendportal.templates.edit', $template->id));

        $response->assertOk();

        $response->assertSee($template->name);
        $response->assertSee($template->content);
    }

    // Update.

    /** @test */
    public function a_guest_cannot_update_a_template()
    {
        $template = factory(Template::class)->create();

        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('templates', $data);
        $this->assertDatabaseHas('templates', [
            'id' => $template->id,
            'workspace_id' => $template->workspace_id,
            'name' => $template->name,
            'content' => $template->content
        ]);
    }

    /** @test */
    public function a_logged_in_user_can_update_a_template()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertRedirect(route('sendportal.templates.index'));

        $this->assertDatabaseMissing('templates', [
            'id' => $template->id,
            'name' => $template->name,
            'content' => $template->content
        ]);
        $this->assertDatabaseHas('templates', $data + ['id' => $template->id, 'workspace_id' => $user->currentWorkspace()->id]);
    }

    /** @test */
    public function updates_are_validated()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertSessionHasErrors('content');

        $data = [
            'content' => $this->faker->sentence
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertSessionHasErrors('name');
    }

    // Destroy.

    /** @test */
    public function a_logged_in_user_can_delete_a_template()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $response = $this->delete(route('sendportal.templates.destroy', $template->id));

        $response->assertRedirect(route('sendportal.templates.index'));

        $this->assertDatabaseMissing('templates', [
            'id' => $template->id,
            'name' => $template->name
        ]);
    }

    /** @test */
    public function a_logged_in_user_cannot_delete_a_template_if_it_is_used()
    {
        $user = $this->createUserWithWorkspace();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'workspace_id' => $user->currentWorkspace()->id
        ]);

        $campaign = factory(Campaign::class)->create([
            'template_id' => $template->id
        ]);

        $response = $this->from(route('sendportal.templates.index'))
            ->delete(route('sendportal.templates.destroy', $template->id));

        $response->assertRedirect(route('sendportal.templates.index'))
            ->assertSessionHasErrors(['template']);

        $this->assertDatabaseHas('templates', [
            'id' => $template->id,
            'name' => $template->name
        ]);
    }
}
