<?php

declare(strict_types=1);

namespace Tests\Feature\Subscribers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Tag;
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
        // given
        $file = UploadedFile::fake()->image('subscribers.jpg');

        // when
        $response = $this->post(route('sendportal.subscribers.import.store'), [
            'file' => $file
        ]);

        // then
        $response->assertSessionHasErrors('file');
    }

    /** @test */
    public function it_should_store_the_uploaded_file_process_the_subscribers_and_redirect_to_subscribers_index_page_if_there_are_no_errors()
    {
        // given
        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        // when
        $response = $this->post(route('sendportal.subscribers.import.store'), [
            'file' => $file
        ]);

        // then
        $response->assertRedirect(route('sendportal.subscribers.index'));

        $response->assertSessionHas('success');

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
        // given
        $file = $this->createFakeCsvFile([
            ['', 'wrong_email', 'Foo', 'Bar'],                  // bad
            ['', 'test@email.com', 'Test Name', 'Test Surname'] // good
        ]);

        // when
        $response = $this
            ->from(route('sendportal.subscribers.import'))
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ]);

        // then
        $response->assertRedirect(route('sendportal.subscribers.import'));

        $response->assertSessionHas('errors');

        $this->assertDatabaseCount('sendportal_subscribers', 0);
    }

    /** @test */
    public function it_should_allow_attaching_subscribers_to_an_existing_tag()
    {
        // given
        $tag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        // when
        $response = $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'tags' => [$tag->id]
            ]);

        // then
        $response->assertRedirect(route('sendportal.subscribers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('sendportal_subscribers', 1);
        $this->assertDatabaseCount('sendportal_tag_subscriber', 1);
    }

    /** @test */
    public function it_should_allow_attaching_subscribers_to_multiple_tag()
    {
        // given
        $tags = Tag::factory()->count(2)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        // when
        $response = $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'tags' => $tags->pluck('id')->toArray()
            ]);

        // then
        $response->assertRedirect(route('sendportal.subscribers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('sendportal_subscribers', 1);
        $this->assertDatabaseCount('sendportal_tag_subscriber', 2);
    }

    /** @test */
    public function it_should_allow_updating_an_existing_subscriber_if_the_id_column_is_filled()
    {
        // given
        $subscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            [$subscriber->id, 'test@email.com', 'Test Name', 'Test Surname']
        ]);

        // when
        $response = $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ]);

        $subscriber->refresh();

        // then
        $response->assertRedirect(route('sendportal.subscribers.index'));
        $response->assertSessionHas('success');

        self::assertEquals('test@email.com', $subscriber->email);
        self::assertEquals('Test Name', $subscriber->first_name);
        self::assertEquals('Test Surname', $subscriber->last_name);
    }

    /** @test */
    public function it_should_allow_updating_an_existing_subscriber_by_email_if_it_already_exist()
    {
        // given
        $subscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', $subscriber->email, 'Test Name', 'Test Surname']
        ]);

        // when
        $response = $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ]);

        $subscriber->refresh();

        // then
        $response->assertRedirect(route('sendportal.subscribers.index'));
        $response->assertSessionHas('success');

        self::assertEquals('Test Name', $subscriber->first_name);
        self::assertEquals('Test Surname', $subscriber->last_name);
    }

    /** @test */
    public function it_should_notify_the_user_of_how_many_subscribers_have_been_imported_and_how_many_have_been_updated()
    {
        // given
        $subscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', 'test@email.com', 'Test Name', 'Test Surname'], // create
            ['', $subscriber->email, $subscriber->name, 'Update Surname'], // update
            ['', 'test2@email.com', 'Test Name', 'Test Surname'] // create
        ]);

        // when
        $response = $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file
            ]);

        // then
        $response->assertRedirect(route('sendportal.subscribers.index'));
        $response->assertSessionHas('success', 'Imported 2 subscriber(s) and updated 1 subscriber(s) out of 3');
    }

    /** @test */
    public function it_should_not_remove_existing_tags_from_a_subscriber_when_importing_subscribers_with_a_new_tag()
    {
        // given
        $subscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $subscriber->tags()->attach(
            Tag::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ])
        );

        $tag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $file = $this->createFakeCsvFile([
            ['', $subscriber->email, 'Test Name', 'Test Surname']
        ]);

        // when
        $response = $this
            ->post(route('sendportal.subscribers.import.store'), [
                'file' => $file,
                'tags' => [$tag->id]
            ]);

        $subscriber->refresh();

        // then
        $response->assertRedirect(route('sendportal.subscribers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('sendportal_tag_subscriber', 2);
    }

    protected function createFakeCsvFile(array $rows)
    {
        $rows = array_map(
            static function ($row) {
                return implode(',', $row);
            },
            $rows
        );

        array_unshift($rows, implode(',', ['id', 'email', 'first_name', 'last_name']));

        return UploadedFile::fake()->createWithContent('subscribers.csv', implode("\n", $rows));
    }
}
