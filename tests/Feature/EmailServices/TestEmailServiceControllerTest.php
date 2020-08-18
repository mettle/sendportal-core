<?php

namespace Tests\Feature\EmailServices;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Services\Messages\DispatchTestMessage;
use Sendportal\Base\Services\Messages\MessageOptions;
use Tests\TestCase;

class TestEmailServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_test_email_service_page_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $service = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.email_services.test.create', $service->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function an_email_service_cannot_be_tested_without_a_from_email_address()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        $this
            ->actingAs($user)
            ->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), [
                'to' => $user->email,
                'subject' => 'test',
                'body' => 'test'
            ])
            ->assertRedirect(route('sendportal.email_services.test.create', $emailService->id))
            ->assertSessionHasErrors('from');
    }

    /** @test */
    public function an_email_service_cannot_be_tested_without_a_subject()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        $this
            ->actingAs($user)
            ->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), [
                'to' => $user->email,
                'subject' => '',
                'body' => 'test'
            ])
            ->assertRedirect(route('sendportal.email_services.test.create', $emailService->id))
            ->assertSessionHasErrors('subject');
    }

    /** @test */
    public function an_email_service_cannot_be_tested_without_a_body()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        $this
            ->actingAs($user)
            ->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), [
                'to' => $user->email,
                'subject' => 'test',
                'body' => ''
            ])
            ->assertRedirect(route('sendportal.email_services.test.create', $emailService->id))
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function an_email_service_can_be_tested_by_an_authenticated_user()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        $from = 'test@sendportal.io';

        $this->instance(DispatchTestMessage::class, Mockery::mock(DispatchTestMessage::class, function ($mock) use ($workspace, $emailService, $user, $from) {
            $mock->shouldReceive('testService')
                ->once()
                ->withArgs(function ($workspaceId, $targetService, MessageOptions $options) use ($workspace, $emailService, $user, $from) {
                    return $workspaceId === $workspace->id
                        && $targetService->id === $emailService->id
                        && $options->getTo() === $user->email
                        && $options->getFromEmail() === $from;
                })
                ->andReturn(1);
        }));

        // when
        $this->actingAs($user)
            ->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), [
                'to' => $user->email,
                'from' => $from,
                'subject' => 'test',
                'body' => 'test'
            ])
            ->assertRedirect(route('sendportal.email_services.index'))
            ->assertSessionHas('success');
    }

    /** @test */
    public function it_should_redirect_the_user_to_the_email_service_test_page_if_the_email_service_test_fails()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $emailService = factory(EmailService::class)->create(['workspace_id' => $workspace->id]);

        $from = 'test@sendportal.io';

        $this->instance(DispatchTestMessage::class, Mockery::mock(DispatchTestMessage::class, function ($mock) use ($workspace, $emailService, $user, $from) {
            $mock->shouldReceive('testService')
                ->once()
                ->withArgs(function ($workspaceId, $targetService, MessageOptions $options) use ($workspace, $emailService, $user, $from) {
                    return $workspaceId === $workspace->id
                        && $targetService->id === $emailService->id
                        && $options->getTo() === $user->email
                        && $options->getFromEmail() === $from;
                })
                ->andThrow(new Exception('whoops'));
        }));

        // when
        $this->actingAs($user)
            ->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), [
                'to' => $user->email,
                'from' => $from,
                'subject' => 'test',
                'body' => 'test'
            ])
            ->assertRedirect(route('sendportal.email_services.test.create', $emailService->id))
            ->assertSessionHas('error', 'Response: whoops');
    }
}
