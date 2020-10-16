<?php

declare(strict_types=1);

namespace Tests\Feature\Subscribers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Sendportal\Base\Facades\Sendportal;
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
        // when
        $response = $this->get(route('sendportal.subscribers.import'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function it_should_not_allow_any_file_other_than_a_csv_file_to_be_uploaded()
    {
        $file = UploadedFile::fake()->image('subscribers.jpg');

        $this->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertSessionHasErrors('file');
    }

    /** @test */
    public function it_should_store_the_uploaded_file_process_the_subscribers_and_redirect_to_subscribers_index_page_if_there_are_no_errors()
    {
        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('sendportal_subscribers', [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email' => 'test@email.com',
            'first_name' => 'Test Name',
            'last_name' => 'Test Surname'
        ]);
    }

    /** @test */
    public function it_should_not_import_subscribers_as_the_upload_has_to_be_validated_before_storing_subscribers()
    {
        $file = $this->createFakeCsvFile([
            ['', 'wrong_email', 'Foo', 'Bar'],                  // bad
            ['', 'test@email.com', 'Test Name', 'Test Surname'] // good
        ]);

        $this
            ->from(route('sendportal.subscribers.import'))
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertRedirect(route('sendportal.subscribers.import'))
            ->assertSessionHas('errors');

        $this->assertDatabaseCount('sendportal_subscribers', 0);
    }

    /** @test */
    public function it_should_allow_attaching_subscribers_to_an_existing_segment()
    {
        $segment = factory(Segment::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'segments' => [$segment->id]
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('sendportal_subscribers', 1);
        $this->assertDatabaseCount('sendportal_segment_subscriber', 1);
    }

    /** @test */
    public function it_should_allow_attaching_subscribers_to_multiple_segment()
    {
        $segments = factory(Segment::class, 2)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'segments' => $segments->pluck('id')->toArray()
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('sendportal_subscribers', 1);
        $this->assertDatabaseCount('sendportal_segment_subscriber', 2);
    }

    /** @test */
    public function it_should_allow_updating_an_existing_subscriber_if_the_id_column_is_filled()
    {
        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            [$subscriber->id, 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        $this
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
        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', $subscriber->email, 'Test Name', 'Test Surname']
        ]);

        $this
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
    public function it_should_notify_the_user_of_how_many_subscribers_have_been_imported_and_how_many_have_been_updated()
    {
        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname'], // create
            ['', $subscriber->email, $subscriber->name, 'Update Surname'], // update
            ['', 'test2@email.com', 'Test Name', 'Test Surname'] // create
        ]);

        $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success', 'Imported 2 subscriber(s) and updated 1 subscriber(s) out of 3');
    }

    /** @test */
    public function it_should_not_remove_existing_segments_from_a_subscriber_when_importing_subscribers_with_a_new_segment()
    {
        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $subscriber->segments()->attach(
            factory(Segment::class)->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ])
        );

        $segment = factory(Segment::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', $subscriber->email, 'Test Name', 'Test Surname']
        ]);

        $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'segments' => [$segment->id]
            ])
            ->assertRedirect(route('sendportal.subscribers.index'))
            ->assertSessionHas('success');

        $subscriber->refresh();

        $this->assertDatabaseCount('sendportal_segment_subscriber', 2);
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
