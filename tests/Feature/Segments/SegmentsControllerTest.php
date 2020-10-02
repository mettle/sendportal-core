<?php

declare(strict_types=1);

namespace Tests\Feature\Segments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;
use Tests\TestCase;

class SegmentsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function the_index_of_segments_is_accessible_to_authenticated_users()
    {
        factory(Segment::class, 3)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.segments.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    function the_segment_create_form_is_accessible_to_authenticated_users()
    {
        // when
        $response = $this->get(route('sendportal.segments.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    function new_segments_can_be_created_by_authenticated_users()
    {
        $segmentStoreData = [
            'name' => $this->faker->word
        ];

        // when
        $response = $this
            ->post(route('sendportal.segments.store'), $segmentStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('segments', [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'name' => $segmentStoreData['name']
        ]);
    }

    /** @test */
    function the_segment_edit_view_is_accessible_by_authenticated_users()
    {
        $segment = factory(Segment::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.segments.edit', $segment->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function a_segment_is_updateable_by_an_authenticated_user()
    {
        $segment = factory(Segment::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $segmentUpdateData = [
            'name' => $this->faker->word
        ];

        // when
        $response = $this
            ->put(route('sendportal.segments.update', $segment->id), $segmentUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('segments', [
            'id' => $segment->id,
            'name' => $segmentUpdateData['name']
        ]);
    }

    /* @test */
    public function a_segment_can_be_deleted()
    {
        $segment = factory(Segment::class)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $response = $this
            ->delete(route('sendportal.segments.destroy', $segment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('segments', [
            'id' => $segment->id,
        ]);
    }
}
