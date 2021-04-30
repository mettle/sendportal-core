<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Aws\Sdk;
use Aws\Ses\SesClient;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class QuotaServiceTest extends TestCase
{
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

        $this->mockMailAdapter(2);

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function ses_message_limit_has_been_reached()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(1, 1);

        // then
        self::assertTrue($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function ses_message_limit_quota_not_available()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter();

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function ses_message_limit_unlimited_quota()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(-1);

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function smtp_hourly_message_limit_reached()
    {
        // given
        $emailService = EmailService::factory()->create(
            [
                'type_id' => EmailServiceType::SMTP,
                'settings' => [
                    'quota_limit' => 5,
                    'quota_period' => EmailService::QUOTA_PERIOD_HOUR,
                ],
            ]
        );

        $campaign = Campaign::factory()->create(
            [
                'email_service_id' => $emailService->id,
                'workspace_id' => $emailService->workspace_id,
            ]
        );

        Message::factory()->count(5)->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
                'sent_at' => now()->subMinute(),
            ]
        );

        // then
        self::assertTrue($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function smtp_hourly_message_limit_not_reached()
    {
        // given
        $emailService = EmailService::factory()->create(
            [
                'type_id' => EmailServiceType::SMTP,
                'settings' => [
                    'quota_limit' => 5,
                    'quota_period' => EmailService::QUOTA_PERIOD_HOUR,
                ],
            ]
        );

        $campaign = Campaign::factory()->create(
            [
                'email_service_id' => $emailService->id,
                'workspace_id' => $emailService->workspace_id,
            ]
        );

        Message::factory()->count(3)->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
                'sent_at' => now()->subHours(2),
            ]
        );

        Message::factory()->count(2)->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
                'sent_at' => now()->subMinute(),
            ]
        );

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function smtp_daily_message_limit_reached()
    {
        // given
        $emailService = EmailService::factory()->create(
            [
                'type_id' => EmailServiceType::SMTP,
                'settings' => [
                    'quota_limit' => 5,
                    'quota_period' => EmailService::QUOTA_PERIOD_DAY,
                ],
            ]
        );

        $campaign = Campaign::factory()->create(
            [
                'email_service_id' => $emailService->id,
                'workspace_id' => $emailService->workspace_id,
            ]
        );

        Message::factory()->count(5)->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
                'sent_at' => now()->subHours(12),
            ]
        );

        // then
        self::assertTrue($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function smtp_daily_message_limit_not_reached()
    {
        // given
        $emailService = EmailService::factory()->create(
            [
                'type_id' => EmailServiceType::SMTP,
                'settings' => [
                    'quota_limit' => 5,
                    'quota_period' => EmailService::QUOTA_PERIOD_DAY,
                ],
            ]
        );

        $campaign = Campaign::factory()->create(
            [
                'email_service_id' => $emailService->id,
                'workspace_id' => $emailService->workspace_id,
            ]
        );

        Message::factory()->count(3)->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
                'sent_at' => now()->subHours(2),
            ]
        );

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    /** @test */
    public function smtp_message_limit_not_set()
    {
        // given
        $emailService = EmailService::factory()->create(
            [
                'type_id' => EmailServiceType::SMTP,
            ]
        );

        $campaign = Campaign::factory()->create(
            [
                'email_service_id' => $emailService->id,
                'workspace_id' => $emailService->workspace_id,
            ]
        );

        Message::factory()->count(3)->create(
            [
                'source_id' => $campaign->id,
                'workspace_id' => $emailService->workspace_id,
                'sent_at' => now()->subHours(2),
            ]
        );

        // then
        self::assertFalse($this->quotaService->hasReachedMessageLimit($emailService));
    }

    protected function mockMailAdapter(int $quota = null, int $sent = 0): void
    {
        $sendQuota = [];

        if ($quota) {
            $sendQuota = [
                'Max24HourSend' => $quota,
                'SentLast24Hours' => $sent,
            ];
        }

        $sesClient = $this->getMockBuilder(SesClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sesClient->method('__call')->willReturn(collect($sendQuota));

        $aws = $this->getMockBuilder(Sdk::class)->getMock();
        $aws->method('createClient')->willReturn($sesClient);

        $this->app->singleton('aws', function () use ($aws) {
            return $aws;
        });
    }
}
