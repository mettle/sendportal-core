<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Aws\Sdk;
use Aws\Ses\SesClient;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
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
    public function fewer_subscribers_than_quota_available()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(2);

        // then
        self::assertFalse($this->quotaService->exceedsQuota($emailService, 1));
    }

    /** @test */
    public function more_subscribers_than_quota_available()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(1);

        // then
        self::assertTrue($this->quotaService->exceedsQuota($emailService, 2));
    }

    /** @test */
    public function send_quota_not_available()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter();

        // then
        self::assertFalse($this->quotaService->exceedsQuota($emailService, 1));
    }

    /** @test */
    public function unlimited_quota()
    {
        // given
        $emailService = EmailService::factory()->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(-1);

        // then
        self::assertFalse($this->quotaService->exceedsQuota($emailService, 1));
    }

    protected function mockMailAdapter(int $quota = null): void
    {
        $sendQuota = [];

        if ($quota) {
            $sendQuota = [
                'Max24HourSend' => $quota,
                'SentLast24Hours' => 0,
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
