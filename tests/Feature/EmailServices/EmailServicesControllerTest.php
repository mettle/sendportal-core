<?php

declare(strict_types=1);

namespace Tests\Feature\EmailServices;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Tests\TestCase;

class EmailServicesControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function the_index_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        factory(EmailService::class, 3)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.email_services.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    function the_provider_create_form_is_accessible_to_authenticated_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        // when
        $response = $this->actingAs($user)->get(route('sendportal.email_services.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    function new_email_services_can_be_created_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $emailServiceStoreData = [
            'name' => $this->faker->word,
            'type_id' => EmailServiceType::POSTMARK,
            'settings' => [
                'key' => Str::random()
            ]
        ];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.email_services.store'), $emailServiceStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('email_services', [
            'workspace_id' => $workspace->id,
            'name' => $emailServiceStoreData['name'],
            'type_id' => $emailServiceStoreData['type_id']
        ]);
    }

    /** @test */
    function the_email_service_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.email_services.edit', $emailService->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function an_email_service_is_updateable_by_an_authenticated_user()
    {
        $this->withoutExceptionHandling();
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        $emailServiceUpdateData = [
            'name' => $this->faker->word,
            'settings' => [
                'key' => Str::random()
            ]
        ];

        // when
        $response = $this->actingAs($user)
            ->put(route('sendportal.email_services.update', $emailService->id), $emailServiceUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('email_services', [
            'id' => $emailService->id,
            'name' => $emailServiceUpdateData['name']
        ]);
    }

    /** @test */
    function an_email_service_can_be_deleted_by_an_authenticated_user()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        // when
        $this->actingAs($user)
            ->delete(route('sendportal.email_services.delete', $emailService->id));

        // then
        $this->assertDatabaseMissing('email_services', [
            'id' => $emailService->id
        ]);
    }

    /** @test */
    function email_services_require_the_correct_settings_to_be_saved()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $emailServiceStoreData = [
            'name' => $this->faker->word,
            'type_id' => EmailServiceType::POSTMARK,
        ];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.email_services.store'), $emailServiceStoreData);

        // then
        $response->assertRedirect();
        $response->assertSessionHasErrors(['settings.key']);
    }

    /** @test */
    function email_services_cannot_be_deleted_if_they_are_being_used()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        factory(Campaign::class)->create([
            'workspace_id' => $workspace->id,
            'email_service_id' => $emailService->id
        ]);

        // when
        $response = $this->actingAs($user)
            ->delete(route('sendportal.email_services.delete', $emailService->id));

        // then
        $response->assertRedirect();
        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('email_services', [
            'id' => $emailService->id
        ]);
    }
}
