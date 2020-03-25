<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Models\Provider;
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
     * Dispatch the email via the given provider.
     *
     * @throws Exception
     */
    public function handle(string $mergedContent, MessageOptions $messageOptions, Provider $provider): string
    {
        return $this->mailAdapter->adapter($provider)
            ->send(
                $messageOptions->getFrom(),
                $messageOptions->getTo(),
                $messageOptions->getSubject(),
                $messageOptions->getTrackingOptions(),
                $mergedContent
            );
    }
}
