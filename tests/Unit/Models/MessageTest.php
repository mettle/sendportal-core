<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\MessageFailure;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function deleting_a_message_also_deletes_its_associated_failures()
    {
        // given
        $message = Message::factory()->create();
        $message->failures()->create();

        // when
        $message->delete();

        // then
        static::assertCount(0, Message::all());
        static::assertCount(0, MessageFailure::all());
    }
}
