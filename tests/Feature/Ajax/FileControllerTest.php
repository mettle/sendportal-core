<?php

namespace Tests\Feature\Ajax;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    /** @test */
    public function the_image_upload_endpoint_is_not_accessible_to_guest_users()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('image.jpg');

        $this
            ->json('POST', route('sendportal.ajax.file.store'), [
                'image' => $file,
            ])
            ->assertUnauthorized();

        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    /** @test */
    public function images_bigger_than_2048_kb_are_not_allowed_for_upload()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('image.jpg')->size(2048 + 1);

        $user = $this->createUserWithWorkspace();

        $this
            ->actingAs($user)
            ->json('POST', route('sendportal.ajax.file.store'), [
                'file' => $file,
            ])
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function documents_are_not_allowed_for_upload()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $user = $this->createUserWithWorkspace();

        $this
            ->actingAs($user)
            ->json('POST', route('sendportal.ajax.file.store'), [
                'file' => $file,
            ])
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_should_allow_image_uploads_to_authenticated_users()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('image.jpg');

        $user = $this->createUserWithWorkspace();

        $this
            ->actingAs($user)
            ->json('POST', route('sendportal.ajax.file.store'), [
                'file' => $file,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'file'
            ]);

        Storage::disk('public')->assertExists('images/' . $file->hashName());
    }
}
