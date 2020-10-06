<?php

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Workspace;
use Tests\TestCase;

class CampaignDispatchControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function campaigns_can_be_dispatched_to_segments_belonging_to_the_users_workspace()
    {
        $workspace = factory(Workspace::class)->create();
        $user = $this->createWorkspaceUser($workspace);

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);

        $response = $this->actingAs($user)->put(route('sendportal.campaigns.send', $campaign->id), [
            'recipients' => 'send_to_segments',
            'segments' => [$validSegment->id],
        ]);

        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function campaigns_cannot_be_dispatched_to_segments_belonging_to_another_workspace()
    {
        $workspace = factory(Workspace::class)->create();
        $anotherWorkspace = factory(Workspace::class)->create();
        $user = $this->createWorkspaceUser($workspace);

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $invalidSegment = factory(Segment::class)->create([
            'workspace_id' => $anotherWorkspace->id,
        ]);

        $response = $this->actingAs($user)->put(route('sendportal.campaigns.send', $campaign->id), [
            'recipients' => 'send_to_segments',
            'segments' => [$validSegment->id, $invalidSegment->id],
        ]);

        $response->assertSessionHasErrors([
            'segments' => 'One or more of the segments is invalid.',
        ]);
    }
}
