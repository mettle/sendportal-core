<?php

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
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
        $campaign = factory(Campaign::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $response = $this->put(route('sendportal.campaigns.send', $campaign->id), [
            'recipients' => 'send_to_segments',
            'segments' => [$validSegment->id],
        ]);

        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function campaigns_cannot_be_dispatched_to_segments_belonging_to_another_workspace()
    {
        $campaign = factory(Campaign::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $invalidSegment = factory(Segment::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId() + 1,
        ]);

        $response = $this->put(route('sendportal.campaigns.send', $campaign->id), [
            'recipients' => 'send_to_segments',
            'segments' => [$validSegment->id, $invalidSegment->id],
        ]);

        $response->assertSessionHasErrors([
            'segments' => 'One or more of the segments is invalid.',
        ]);
    }
}
