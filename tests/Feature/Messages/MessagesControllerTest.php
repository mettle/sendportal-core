<?php

declare(strict_types=1);

namespace Tests\Feature\Messages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class MessagesControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_index_of_sent_messages_is_accessible_to_an_authenticated_user()
    {
        // given
        Message::factory()->count(3)->create(['workspace_id' => Sendportal::currentWorkspaceId(), 'sent_at' => now()]);

        // when
        $response = $this->get(route('sendportal.messages.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function the_index_of_draft_messages_is_accessible_to_an_authenticated_user()
    {
        // given
        Message::factory()->count(3)->create(['workspace_id' => Sendportal::currentWorkspaceId(), 'sent_at' => null]);

        // when
        $response = $this->get(route('sendportal.messages.draft'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_draft_message_can_be_viewed_by_an_authenticated_user()
    {
        // given
        $campaign = Campaign::factory()->withContent()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $message = Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'source_id' => $campaign->id,
            'sent_at' => null
        ]);

        // when
        $response = $this->get(route('sendportal.messages.show', $message->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_draft_message_can_be_deleted()
    {
        // given
        $campaign = Campaign::factory()->withContent()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $message = Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'source_id' => $campaign->id,
            'sent_at' => null
        ]);

        // when
        $this->delete(route('sendportal.messages.delete', $message->id))
            ->assertRedirect(route('sendportal.messages.draft'));

        // then
        $this->assertDatabaseMissing('sendportal_messages', ['id' => $message->id]);
    }

    /** @test */
    public function a_sent_message_cannot_be_deleted()
    {
        // given
        $campaign = Campaign::factory()->withContent()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $message = Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'source_id' => $campaign->id,
            'sent_at' => now()
        ]);

        // when
        $this
            ->from(route('sendportal.messages.draft'))
            ->delete(route('sendportal.messages.delete', $message->id))
            ->assertRedirect(route('sendportal.messages.draft'));

        // then
        $this->assertDatabaseHas('sendportal_messages', ['id' => $message->id]);
    }

    /**
     * @test
     * https://github.com/mettle/sendportal/issues/90
     */
    public function a_message_can_be_sent_when_other_messages_have_been_sent()
    {
        // given
        $workspaceId = Sendportal::currentWorkspaceId();

        $campaign = Campaign::factory()->withContent()->create(['workspace_id' => $workspaceId]);

        Message::factory()->create([
            'workspace_id' => $workspaceId,
            'source_id' => $campaign->id,
            'sent_at' => now(), // Message already sent.
        ]);

        $draftMessage = Message::factory()->create([
            'workspace_id' => $workspaceId,
            'source_id' => $campaign->id,
            'queued_at' => now(),
        ]);

        // when
        $this->post(route('sendportal.messages.send'), ['id' => $draftMessage->id])
            ->assertRedirect(route('sendportal.messages.draft'))
            ->assertSessionHas('success');

        $draftMessage->refresh();

        // then
        self::assertNotNull($draftMessage->sent_at);
    }
}
