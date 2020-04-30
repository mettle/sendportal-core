<?php

declare(strict_types=1);

namespace Tests\Feature\Providers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\ProviderType;
use Tests\TestCase;

class ProvidersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function the_index_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        factory(Provider::class, 3)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.providers.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    function the_provider_create_form_is_accessible_to_authenticated_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        // when
        $response = $this->actingAs($user)->get(route('sendportal.providers.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    function new_providers_can_be_created_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $providerStoreData = [
            'name' => $this->faker->word,
            'type_id' => ProviderType::POSTMARK,
            'settings' => [
                'key' => Str::random()
            ]
        ];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.providers.store'), $providerStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('providers', [
            'workspace_id' => $workspace->id,
            'name' => $providerStoreData['name'],
            'type_id' => $providerStoreData['type_id']
        ]);
    }

    /** @test */
    function the_segment_edit_view_is_accessible_by_authenticated_users()
    {
        $this->withoutExceptionHandling();

        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $provider = factory(Provider::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.providers.edit', $provider->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function a_provider_is_updateable_by_an_authenticated_user()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $provider = factory(Provider::class)->create(['workspace_id' => $workspace->id]);

        $providerUpdateData = [
            'name' => $this->faker->word,
            'type_id' => ProviderType::POSTMARK,
            'settings' => [
                'key' => Str::random()
            ]
        ];

        // when
        $response = $this->actingAs($user)
            ->put(route('sendportal.providers.update', $provider->id), $providerUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'name' => $providerUpdateData['name'],
            'type_id' => $providerUpdateData['type_id']
        ]);
    }

    /** @test */
    function a_provider_can_be_deleted_by_an_authenticated_user()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $provider = factory(Provider::class)->create(['workspace_id' => $workspace->id]);

        // when
        $this->actingAs($user)
            ->delete(route('sendportal.providers.delete', $provider->id));

        // then
        $this->assertDatabaseMissing('providers', [
            'id' => $provider->id
        ]);
    }

    /** @test */
    function providers_require_the_correct_settings_to_be_saved()
    {
        // given
        $user = $this->createUserWithWorkspace();

        $providerStoreData = [
            'name' => $this->faker->word,
            'type_id' => ProviderType::POSTMARK,
        ];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.providers.store'), $providerStoreData);

        // then
        $response->assertRedirect();
        $response->assertSessionHasErrors(['settings.key']);
    }

    /** @test */
    function providers_cannot_be_deleted_if_they_are_being_used()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $provider = factory(Provider::class)->create(['workspace_id' => $workspace->id]);

        factory(Campaign::class)->create([
            'workspace_id' => $workspace->id,
            'provider_id' => $provider->id
        ]);

        // when
        $response = $this->actingAs($user)
            ->delete(route('sendportal.providers.delete', $provider->id));

        // then
        $response->assertRedirect();
        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id
        ]);
    }
}
