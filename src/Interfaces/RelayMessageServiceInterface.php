<?php

namespace Sendportal\Base\Interfaces;

use Exception;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Services\Messages\MessageOptions;

interface RelayMessageServiceInterface
{
    /**
     * Dispatch the email via the email service.
     *
     * @throws Exception
     */
    public function handle(string $mergedContent, MessageOptions $messageOptions, EmailService $emailService): string;
}
