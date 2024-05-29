<?php

namespace Sendportal\Base\Traits;

use Aws\Sdk;
use Aws\Ses\SesClient;

trait MocksSesMailAdapter
{
    /*
     * We need to mock the SES mail adapter for tests that could cause QuotaService::hasReachedMessageLimit()
     * to be called to prevent any attempt to query the API.
     */
    protected function mockSesMailAdapter(int $quota = null, int $sent = 0): void
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
