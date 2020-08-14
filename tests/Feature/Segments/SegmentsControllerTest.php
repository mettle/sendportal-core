<?php

declare(strict_types=1);

namespace Tests\Feature\Segments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class SegmentsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function the_index_of_segments_is_accessible_to_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        factory(Segment::class, 3)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.segments.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function the_segment_create_form_is_accessible_to_authenticated_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        // when
        $response = $this->actingAs($user)->get(route('sendportal.segments.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function new_segments_can_be_created_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $segmentStoreData = [
            'name' => $this->faker->word
        ];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.segments.store'), $segmentStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('segments', [
            'workspace_id' => $workspace->id,
            'name' => $segmentStoreData['name']
        ]);
    }

    /** @test */
    public function the_segment_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $segment = factory(Segment::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.segments.edit', $segment->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_segment_is_updateable_by_an_authenticated_user()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $segment = factory(Segment::class)->create(['workspace_id' => $workspace->id]);

        $segmentUpdateData = [
            'name' => $this->faker->word
        ];

        // when
        $response = $this->actingAs($user)
            ->put(route('sendportal.segments.update', $segment->id), $segmentUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('segments', [
            'id' => $segment->id,
            'name' => $segmentUpdateData['name']
        ]);
    }

    /** @test */
    function subscribers_are_not_synced_when_the_segment_is_updated()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $subscribers = factory(Subscriber::class, 5)->create([
            'workspace_id' => $workspace->id,
        ]);
        $segment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id
        ]);
        $segment->subscribers()->attach($subscribers);

        $this->assertCount($subscribers->count(), $segment->subscribers);

        $this->actingAs($user)->put(route('sendportal.segments.update', $segment->id), [
            'name' => 'Very Cool New Name',
        ]);

        $segment->refresh();
        $this->assertCount($subscribers->count(), $segment->subscribers);
    }


    /* @test */
    public function a_segment_can_be_deleted()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $segment = factory(Segment::class)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)
            ->delete(route('sendportal.segments.destroy', $segment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('segments', [
            'id' => $segment->id,
        ]);
    }
}
