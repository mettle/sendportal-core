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
        factory(Message::class, 3)->create(['workspace_id' => Sendportal::currentWorkspaceId(), 'sent_at' => now()]);

        // when
        $response = $this
            ->get(route('sendportal.messages.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function the_index_of_draft_messages_is_accessible_to_an_authenticated_user()
    {
        factory(Message::class, 3)->create(['workspace_id' => Sendportal::currentWorkspaceId(), 'sent_at' => null]);

        // when
        $response = $this
            ->get(route('sendportal.messages.draft'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_draft_message_can_be_viewed_by_an_authenticated_user()
    {
        $campaign = factory(Campaign::class)->state('withContent')->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        /** @var Message $message */
        $message = factory(Message::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'source_id' => $campaign->id,
            'sent_at' => null
        ]);

        // when
        $response = $this
            ->get(route('sendportal.messages.show', $message->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_draft_message_can_be_deleted()
    {
        $campaign = factory(Campaign::class)->state('withContent')->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        /** @var Message $message */
        $message = factory(Message::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'source_id' => $campaign->id,
            'sent_at' => null
        ]);

        $this->delete(route('sendportal.messages.delete', $message->id))
            ->assertRedirect(route('sendportal.messages.draft'));

        $this->assertDatabaseMissing('sendportal_messages', ['id' => $message->id]);
    }

    /** @test */
    public function a_sent_message_cannot_be_deleted()
    {
        $campaign = factory(Campaign::class)->state('withContent')->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        /** @var Message $message */
        $message = factory(Message::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'source_id' => $campaign->id,
            'sent_at' => now()
        ]);

        $this
            ->from(route('sendportal.messages.draft'))
            ->delete(route('sendportal.messages.delete', $message->id))
            ->assertRedirect(route('sendportal.messages.draft'));

        $this->assertDatabaseHas('sendportal_messages', ['id' => $message->id]);
    }
}
