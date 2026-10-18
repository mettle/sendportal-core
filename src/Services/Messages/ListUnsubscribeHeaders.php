<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Illuminate\Support\Str;
use Sendportal\Base\Models\Message;

class ListUnsubscribeHeaders
{
    /**
     * The headers that let mailbox providers offer their own unsubscribe button.
     *
     * List-Unsubscribe (RFC 2369) points at the unsubscribe page, which also accepts
     * the one-click POST. List-Unsubscribe-Post (RFC 8058) is only added for HTTPS
     * links, because one-click unsubscribe is not allowed over plain HTTP.
     *
     * @return array<string, string>
     */
    public static function forMessage(Message $message): array
    {
        $url = route('sendportal.subscriptions.unsubscribe.one-click', $message->hash);

        $headers = ['List-Unsubscribe' => '<' . $url . '>'];

        if (Str::startsWith($url, 'https://')) {
            $headers['List-Unsubscribe-Post'] = 'List-Unsubscribe=One-Click';
        }

        return $headers;
    }
}
