<?php

declare(strict_types=1);

namespace Tests\Feature\Segments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Facades\Sendportal;
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
        Segment::factory()->count(3)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.segments.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function the_segment_create_form_is_accessible_to_authenticated_users()
    {
        // when
        $response = $this->get(route('sendportal.segments.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function new_segments_can_be_created_by_authenticated_users()
    {
        // given
        $segmentStoreData = [
            'name' => $this->faker->word
        ];

        // when
        $response = $this
            ->post(route('sendportal.segments.store'), $segmentStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_segments', [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'name' => $segmentStoreData['name']
        ]);
    }

    /** @test */
    public function the_segment_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        $segment = Segment::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.segments.edit', $segment->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_segment_is_updateable_by_an_authenticated_user()
    {
        // given
        $segment = Segment::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $segmentUpdateData = [
            'name' => $this->faker->word
        ];

        // when
        $response = $this
            ->put(route('sendportal.segments.update', $segment->id), $segmentUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_segments', [
            'id' => $segment->id,
            'name' => $segmentUpdateData['name']
        ]);
    }

    /** @test */
    public function subscribers_are_not_synced_when_the_segment_is_updated()
    {
        // given
        $subscribers = Subscriber::factory()->count(5)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $segment = Segment::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        $segment->subscribers()->attach($subscribers);

        self::assertCount($subscribers->count(), $segment->subscribers);

        // when
        $this->put(route('sendportal.segments.update', $segment->id), [
            'name' => 'Very Cool New Name',
        ]);

        $segment->refresh();

        // then
        self::assertCount($subscribers->count(), $segment->subscribers);
    }


    /* @test */
    public function a_segment_can_be_deleted()
    {
        // given
        $segment = Segment::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this
            ->delete(route('sendportal.segments.destroy', $segment->id));

        // then
        $response->assertRedirect();

        $this->assertDatabaseMissing('sendportal_segments', [
            'id' => $segment->id,
        ]);
    }

    /** @test */
    public function a_segment_name_must_be_unique_for_a_workspace()
    {
        // given
        $segment = Segment::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $request = [
            'name' => $segment->name,
        ];

        // when
        $response = $this->post(route('sendportal.segments.store'), $request);

        // then
        $response->assertRedirect()
            ->assertSessionHasErrors('name');

        self::assertEquals(1, Segment::where('name', $segment->name)->count());
    }
}
