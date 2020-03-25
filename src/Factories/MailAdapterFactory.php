<?php

declare(strict_types=1);

namespace Sendportal\Base\Factories;

use Sendportal\Base\Adapters\MailgunMailAdapter;
use Sendportal\Base\Adapters\PostmarkMailAdapter;
use Sendportal\Base\Adapters\SendgridMailAdapter;
use Sendportal\Base\Adapters\SesMailAdapter;
use Sendportal\Base\Interfaces\MailAdapterInterface;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\ProviderType;
use InvalidArgumentException;

class MailAdapterFactory
{
    /** @var array */
    public static $adapterMap = [
        ProviderType::SES => SesMailAdapter::class,
        ProviderType::SENDGRID => SendgridMailAdapter::class,
        ProviderType::MAILGUN => MailgunMailAdapter::class,
        ProviderType::POSTMARK => PostmarkMailAdapter::class
    ];

    /**
     * Cache of resolved mail adapters.
     *
     * @var array
     */
    private $adapters = [];

    /**
     * Get a mail adapter instance.
     */
    public function adapter(Provider $provider): MailAdapterInterface
    {
        return $this->adapters[$provider->id] ?? $this->cache($this->resolve($provider), $provider);
    }

    /**
     * Cache a resolved adapter for the given provider.
     */
    private function cache(MailAdapterInterface $adapter, Provider $provider): MailAdapterInterface
    {
        return $this->adapters[$provider->id] = $adapter;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function resolve(Provider $provider): MailAdapterInterface
    {
        if (!$providerType = ProviderType::resolve($provider->type_id)) {
            throw new InvalidArgumentException("Unable to resolve mail provider type from ID [$provider->type_id].");
        }

        $adapterClass = self::$adapterMap[$provider->type_id] ?? null;

        if (!$adapterClass) {
            throw new InvalidArgumentException("Mail adapter type [{$providerType}] is not supported.");
        }

        return new $adapterClass($provider->settings);
    }
}
