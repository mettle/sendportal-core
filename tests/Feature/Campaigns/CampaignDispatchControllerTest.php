<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Exceptions\MessageLimitReachedException;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Tag;
use Tests\TestCase;

class CampaignDispatchControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function campaigns_can_be_dispatched_to_tags_belonging_to_the_users_workspace()
    {
        // given
        $campaign = Campaign::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $validTag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        // when
        $response = $this->put(route('sendportal.campaigns.send', $campaign->id), [
            'recipients' => 'send_to_tags',
            'tags' => [$validTag->id],
        ]);

        // then
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function campaigns_cannot_be_dispatched_to_tags_belonging_to_another_workspace()
    {
        // given
        $campaign = Campaign::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $validTag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $invalidTag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId() + 1,
        ]);

        // when
        $response = $this->put(route('sendportal.campaigns.send', $campaign->id), [
            'recipients' => 'send_to_tags',
            'tags' => [$validTag->id, $invalidTag->id],
        ]);

        // then
        $response->assertSessionHasErrors([
            'tags' => 'One or more of the tags is invalid.',
        ]);
    }

    /** @test */
    public function a_message_limit_reached_exception_is_thrown()
    {
        // given
        $emailService = EmailService::factory()->create(
            [
                'type_id' => EmailServiceType::SMTP,
                'settings' => [
                    'quota_limit' => 5,
                    'quota_period' => EmailService::QUOTA_PERIOD_DAY,
                ],
            ]
        );

        $campaign = Campaign::factory()->create(
            [
                'email_service_id' => $emailService->id,
                'workspace_id' => $emailService->workspace_id,
                'content' => 'test',
            ]
        );

        Message::factory()->count(5)->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
                'sent_at' => now()->subHours(12),
            ]
        );

        $message = Message::factory()->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
            ]
        );

        $this->withoutExceptionHandling()
            ->expectException(MessageLimitReachedException::class);

        $this->post(route('sendportal.messages.send'), ['id' => $message->id]);
    }
}
