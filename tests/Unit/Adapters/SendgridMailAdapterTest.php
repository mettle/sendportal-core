<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use SendGrid\Response;
use Sendportal\Base\Adapters\SendgridMailAdapter;
use Tests\TestCase;

class SendgridMailAdapterTest extends TestCase
{
    public static function messageIdHeaders(): array
    {
        return [
            'HTTP/1.1 casing' => ['X-Message-Id: W86EgYT6SQKk0lRflfLRsA'],
            'HTTP/2 lowercase' => ['x-message-id: W86EgYT6SQKk0lRflfLRsA'],
            'uppercase' => ['X-MESSAGE-ID: W86EgYT6SQKk0lRflfLRsA'],
            'no space after colon' => ['x-message-id:W86EgYT6SQKk0lRflfLRsA'],
        ];
    }

    #[Test]
    #[DataProvider('messageIdHeaders')]
    public function it_resolves_the_message_id_regardless_of_header_case(string $header)
    {
        // given
        $response = new Response(202, '', ['HTTP/2 202', 'server: nginx', $header, '']);

        // when
        $messageId = $this->resolveMessageId($response);

        // then
        self::assertSame('W86EgYT6SQKk0lRflfLRsA', $messageId);
    }

    #[Test]
    public function it_throws_when_there_is_no_message_id_header()
    {
        // given
        $response = new Response(202, '', ['HTTP/2 202', 'server: nginx', '']);

        // then
        $this->expectException(DomainException::class);

        // when
        $this->resolveMessageId($response);
    }

    private function resolveMessageId(Response $response): string
    {
        $method = new ReflectionMethod(SendgridMailAdapter::class, 'resolveMessageId');

        return $method->invoke(new SendgridMailAdapter(), $response);
    }
}
