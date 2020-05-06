<?php

declare(strict_types=1);

namespace Tests\Feature\Webview;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Workspace;
use Tests\TestCase;

class WebviewControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_message_can_be_seen_in_the_webview()
    {
        // given
        $workspace = factory(Workspace::class)->create();
        $campaign = factory(Campaign::class)->state('withContent')->create(['workspace_id' => $workspace->id]);
        $message = factory(Message::class)->create(['source_id' => $campaign->id, 'workspace_id' => $workspace->id]);

        // when
        $response = $this->get(route('sendportal.webview.show', $message->hash));

        // then
        $response->assertOk();
    }
}
