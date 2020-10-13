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
    /**
     * @var QuotaServiceInterface
     */
    protected $quotaService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->quotaService = app(QuotaServiceInterface::class);
    }

    /** @test */
    public function test_fewer_subscribers_than_quota_available()
    {
        $emailService = factory(EmailService::class)->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(2);

        $this->assertFalse($this->quotaService->exceedsQuota($emailService, 1));
    }

    /** @test */
    public function test_more_subscribers_than_quota_available()
    {
        $emailService = factory(EmailService::class)->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(1);

        $this->assertTrue($this->quotaService->exceedsQuota($emailService, 2));
    }

    /** @test */
    public function test_send_quota_not_available()
    {
        $emailService = factory(EmailService::class)->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter();

        $this->assertFalse($this->quotaService->exceedsQuota($emailService, 1));
    }

    /** @test */
    public function test_unlimited_quota()
    {
        $emailService = factory(EmailService::class)->create(['type_id' => EmailServiceType::SES]);

        $this->mockMailAdapter(-1);

        $this->assertFalse($this->quotaService->exceedsQuota($emailService, 1));
    }

    /**
     * @param $quota
     * @return void
     */
    protected function mockMailAdapter($quota = null)
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
