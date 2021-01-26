<?php

declare(strict_types=1);

namespace Tests\Feature\EmailServices;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Sendportal\Base\Facades\Sendportal;
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
        $service = EmailService::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.email_services.test.create', $service->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function an_email_service_cannot_be_tested_without_a_from_email_address()
    {
        // given
        $emailService = EmailService::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $postData = [
            'to' => 'example@example.org',
            'subject' => 'test',
            'body' => 'test'
        ];

        // when
        $response = $this->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), $postData);

        // then
        $response->assertRedirect(route('sendportal.email_services.test.create', $emailService->id));
        $response->assertSessionHasErrors('from');
    }

    /** @test */
    public function an_email_service_cannot_be_tested_without_a_subject()
    {
        // given
        $emailService = EmailService::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $postData = [
            'to' => 'example@example.org',
            'subject' => '',
            'body' => 'test'
        ];

        // when
        $response = $this->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), $postData);

        // then
        $response->assertRedirect(route('sendportal.email_services.test.create', $emailService->id));
        $response->assertSessionHasErrors('subject');
    }

    /** @test */
    public function an_email_service_cannot_be_tested_without_a_body()
    {
        // given
        $emailService = EmailService::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $postData = [
            'to' => 'example@example.org',
            'subject' => 'test',
            'body' => ''
        ];

        // when
        $response = $this->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), $postData);

        // then
        $response->assertRedirect(route('sendportal.email_services.test.create', $emailService->id));
        $response->assertSessionHasErrors('body');
    }

    /** @test */
    public function an_email_service_can_be_tested_by_an_authenticated_user()
    {
        // given
        $emailService = EmailService::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $from = 'test@sendportal.io';

        $postData = [
            'to' => 'example@example.org',
            'from' => $from,
            'subject' => 'test',
            'body' => 'test'
        ];

        $this->instance(DispatchTestMessage::class, Mockery::mock(DispatchTestMessage::class, function ($mock) use ($emailService, $from) {
            $mock->shouldReceive('testService')
                ->once()
                ->withArgs(function ($workspaceId, $targetService, MessageOptions $options) use ($emailService, $from) {
                    return $workspaceId === Sendportal::currentWorkspaceId()
                        && $targetService->id === $emailService->id
                        && $options->getTo() === 'example@example.org'
                        && $options->getFromEmail() === $from;
                })
                ->andReturn(1);
        }));

        // when
        $response = $this->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), $postData);

        // then
        $response->assertRedirect(route('sendportal.email_services.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function it_should_redirect_the_user_to_the_email_service_test_page_if_the_email_service_test_fails()
    {
        // given
        $emailService = EmailService::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $from = 'test@sendportal.io';

        $postData = [
            'to' => 'example@example.org',
            'from' => $from,
            'subject' => 'test',
            'body' => 'test'
        ];

        $this->instance(DispatchTestMessage::class, Mockery::mock(DispatchTestMessage::class, function ($mock) use ($emailService, $from) {
            $mock->shouldReceive('testService')
                ->once()
                ->withArgs(function ($workspaceId, $targetService, MessageOptions $options) use ($emailService, $from) {
                    return $workspaceId === Sendportal::currentWorkspaceId()
                        && $targetService->id === $emailService->id
                        && $options->getTo() === 'example@example.org'
                        && $options->getFromEmail() === $from;
                })
                ->andThrow(new Exception('whoops'));
        }));

        // when
        $response = $this->from(route('sendportal.email_services.test.create', $emailService->id))
            ->post(route('sendportal.email_services.test.store', $emailService->id), $postData);

        // then
        $response->assertRedirect(route('sendportal.email_services.test.create', $emailService->id));
        $response->assertSessionHas('error', 'Response: whoops');
    }
}
