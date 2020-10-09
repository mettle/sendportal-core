<?php

declare(strict_types=1);

namespace Tests\Feature\Subscribers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class SubscribersImportControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /** @test */
    public function the_page_to_upload_subscribers_is_accessible_to_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        // when
        $response = $this->actingAs($user)->get(route('sendportal.subscribers.import'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function it_should_not_allow_any_file_other_than_a_csv_file_to_be_uploaded()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $file = UploadedFile::fake()->image('subscribers.jpg');

        $this
            ->actingAs($user)
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertSessionHasErrors('file');
    }

    /** @test */
    public function it_should_store_the_uploaded_file_process_the_subscribers_and_redirect_to_subscribers_index_page_if_there_are_no_errors()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
            ->actingAs($user)
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subscribers', [
            'workspace_id' => $workspace->id,
            'email' => 'test@email.com',
            'first_name' => 'Test Name',
            'last_name' => 'Test Surname'
        ]);
    }

    /** @test */
    public function it_should_not_import_subscribers_as_the_upload_has_to_be_validated_before_storing_subscribers()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $file = $this->createFakeCsvFile([
            ['', 'wrong_email', 'Foo', 'Bar'],                  // bad
            ['', 'test@email.com', 'Test Name', 'Test Surname'] // good
        ]);

        $this
            ->actingAs($user)
            ->from(route('sendportal.subscribers.import'))
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertRedirect(route('sendportal.subscribers.import'))
            ->assertSessionHas('errors');

        $this->assertDatabaseCount('subscribers', 0);
    }

    /** @test */
    public function it_should_allow_attaching_subscribers_to_an_existing_segment()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $segment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
            ->actingAs($user)
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'segments' => [$segment->id]
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseCount('segment_subscriber', 1);
    }

    /** @test */
    public function it_should_allow_attaching_subscribers_to_multiple_segment()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $segments = factory(Segment::class, 2)->create([
            'workspace_id' => $workspace->id
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
            ->actingAs($user)
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'segments' => $segments->pluck('id')->toArray()
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseCount('segment_subscriber', 2);
    }

    /** @test */
    public function it_should_allow_updating_an_existing_subscriber_if_the_id_column_is_filled()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id
        ]);

        $file = $this->createFakeCsvFile([
            [$subscriber->id, 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
            ->actingAs($user)
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $subscriber->refresh();

        $this->assertEquals('test@email.com', $subscriber->email);
        $this->assertEquals('Test Name', $subscriber->first_name);
        $this->assertEquals('Test Surname', $subscriber->last_name);
    }

    /** @test */
    public function it_should_allow_updating_an_existing_subscriber_by_email_if_it_already_exist()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id
        ]);

        $file = $this->createFakeCsvFile([
            ['', $subscriber->email, 'Test Name', 'Test Surname']
        ]);

        $this
            ->actingAs($user)
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $subscriber->refresh();

        $this->assertEquals('Test Name', $subscriber->first_name);
        $this->assertEquals('Test Surname', $subscriber->last_name);
    }

    /** @test */
    public function it_should_not_remove_existing_segments_from_a_subscriber_when_importing_subscribers_with_a_new_segment()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();

        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id
        ]);

        $subscriber->segments()->attach(
            factory(Segment::class)->create([
                'workspace_id' => $workspace->id
            ])
        );

        $segment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id
        ]);

        $file = $this->createFakeCsvFile([
            ['', $subscriber->email, 'Test Name', 'Test Surname']
        ]);

        $this
            ->actingAs($user)
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'segments' => [$segment->id]
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $subscriber->refresh();

        $this->assertDatabaseCount('segment_subscriber', 2);
    }

    protected function createFakeCsvFile(array $rows)
    {
        $rows = array_map(function ($row) {
            return implode(',', $row);
        }, $rows);

        array_unshift($rows, implode(',', ['id', 'email', 'first_name', 'last_name']));

        return UploadedFile::fake()->createWithContent('subscribers.csv', implode("\n", $rows));
    }
}
