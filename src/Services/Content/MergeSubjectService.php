<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Content;

use Sendportal\Base\Models\Message;
use Sendportal\Base\Traits\NormalizeTags;

class MergeSubjectService
{
    use NormalizeTags;

    public function handle(Message $message): string
    {
        $messageSubject = $this->compileTags($message);

        return $this->mergeSubscriberTags($messageSubject, $message);
    }

    protected function compileTags(Message $message): string
    {
        $tags = [
            'email',
            'first_name',
            'last_name',
        ];

        $messageSubject = $message->subject;

        foreach ($tags as $tag) {
            $messageSubject = $this->normalizeTags($messageSubject, $tag);
        }

        return $messageSubject;
    }

    protected function mergeSubscriberTags(string $messageSubject, Message $message): string
    {
        $tags = [
            'email' => $message->recipient_email,
            'first_name' => $message->subscriber ? $message->subscriber->first_name : '',
            'last_name' => $message->subscriber ? $message->subscriber->last_name : '',
        ];

        foreach ($tags as $key => $replace) {
            $messageSubject = str_ireplace('{{' . $key . '}}', $replace ?: '', $messageSubject);
        }

        return $messageSubject;
    }
}
