<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Sendportal\Base\Exceptions\MessageLimitReachedException;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Traits\MocksSesMailAdapter;
use Tests\TestCase;

class QuotaServiceTest extends TestCase
{
    use MocksSesMailAdapter;

    /** @var QuotaServiceInterface */
    protected $quotaService;

    public function setUp(): void
    {
        parent::setUp();

        $this->quotaService = app(QuotaServiceInterface::class);
    }

    /** @test */
    public function ses_message_limit_has_not_been_reached()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockSesMailAdapter(2);

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function ses_message_limit_has_been_reached()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockSesMailAdapter(1, 1);

        // then
        self::assertTrue($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function ses_message_limit_quota_not_available()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockSesMailAdapter();

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function ses_message_limit_unlimited_quota()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockSesMailAdapter(-1);

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function a_message_limit_reached_exception_is_thrown()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $campaign = Campaign::factory()->create(
            [
                'email_service_id' => $emailService->id,
                'workspace_id' => $emailService->workspace_id,
                'content' => 'test',
            ]
        );

        $message = Message::factory()->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
            ]
        );

        $this->mockSesMailAdapter(1, 1);

        $this->withoutExceptionHandling()
            ->expectException(MessageLimitReachedException::class);

        $this->post(route('sendportal.messages.send'), ['id' => $message->id]);
    }
}
