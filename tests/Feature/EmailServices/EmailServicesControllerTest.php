<?php

declare(strict_types=1);

namespace Tests\Feature\EmailServices;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Tests\TestCase;

class EmailServicesControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function the_provider_create_form_is_accessible_to_authenticated_users()
    {
        // when
        $response = $this->get(route('sendportal.email_services.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function new_email_services_can_be_created_by_authenticated_users()
    {
        $emailServiceStoreData = [
            'name' => $this->faker->word,
            'type_id' => EmailServiceType::POSTMARK,
            'settings' => [
                'key' => Str::random()
            ]
        ];

        // when
        $response = $this
            ->post(route('sendportal.email_services.store'), $emailServiceStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('email_services', [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'name' => $emailServiceStoreData['name'],
            'type_id' => $emailServiceStoreData['type_id']
        ]);
    }

    /** @test */
    public function the_email_service_edit_view_is_accessible_by_authenticated_users()
    {
        $emailService = factory(EmailService::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.email_services.edit', $emailService->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function an_email_service_is_updateable_by_an_authenticated_user()
    {
        $this->withoutExceptionHandling();
        $emailService = factory(EmailService::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $emailServiceUpdateData = [
            'name' => $this->faker->word,
            'settings' => [
                'key' => Str::random()
            ]
        ];

        // when
        $response = $this
            ->put(route('sendportal.email_services.update', $emailService->id), $emailServiceUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('email_services', [
            'id' => $emailService->id,
            'name' => $emailServiceUpdateData['name']
        ]);
    }

    /** @test */
    public function an_email_service_can_be_deleted_by_an_authenticated_user()
    {
        $emailService = factory(EmailService::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $this
            ->delete(route('sendportal.email_services.delete', $emailService->id));

        // then
        $this->assertDatabaseMissing('email_services', [
            'id' => $emailService->id
        ]);
    }

    /** @test */
    public function email_services_require_the_correct_settings_to_be_saved()
    {
        $emailServiceStoreData = [
            'name' => $this->faker->word,
            'type_id' => EmailServiceType::POSTMARK,
        ];

        // when
        $response = $this
            ->post(route('sendportal.email_services.store'), $emailServiceStoreData);

        // then
        $response->assertRedirect();
        $response->assertSessionHasErrors(['settings.key']);
    }

    /** @test */
    public function email_services_cannot_be_deleted_if_they_are_being_used()
    {
        $emailService = factory(EmailService::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        factory(Campaign::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id
        ]);

        // when
        $response = $this
            ->delete(route('sendportal.email_services.delete', $emailService->id));

        // then
        $response->assertRedirect();
        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('email_services', [
            'id' => $emailService->id
        ]);
    }
}
