<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Models\EmailService;
use Exception;

class RelayMessage
{
    /** @var MailAdapterFactory */
    protected $mailAdapter;

    public function __construct(MailAdapterFactory $mailAdapter)
    {
        $this->mailAdapter = $mailAdapter;
    }

    /**
     * Dispatch the email via the email service.
     *
     * @throws Exception
     */
    public function handle(string $mergedContent, MessageOptions $messageOptions, EmailService $emailService): string
    {
        return $this->mailAdapter->adapter($emailService)
            ->send(
                $messageOptions->getFromEmail(),
                $messageOptions->getFromName(),
                $messageOptions->getTo(),
                $messageOptions->getSubject(),
                $messageOptions->getTrackingOptions(),
                $mergedContent
            );
    }
}
