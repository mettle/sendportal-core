<?php

namespace Sendportal\Base\Services\Messages;

use Sendportal\Base\Models\Message;

class MarkAsSent
{
    /**
     * Save the external message_id to the messages table
     */
    public function handle(Message $message, string $messageId): Message
    {
        $message->message_id = $messageId;
        $message->sent_at = now();

        return tap($message)->save();
    }
}
