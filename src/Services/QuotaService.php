<?php

namespace Sendportal\Base\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Adapters\BaseMailAdapter;
use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\ProviderType;

class QuotaService implements QuotaServiceInterface
{
    public function campaignCanBeSent(Campaign $campaign): bool
    {
        switch ($campaign->provider->type_id) {
            case ProviderType::SES:
                return $this->campaignCanBeSentBySes($campaign);
            case ProviderType::SENDGRID:
            case ProviderType::MAILGUN:
            case ProviderType::POSTMARK:
                return true;
        }

        throw new \DomainException('Unrecognised provider type');
    }

    protected function resolveMailAdapter(Provider $provider): BaseMailAdapter
    {
        return app(MailAdapterFactory::class)->adapter($provider);
    }

    protected function campaignCanBeSentBySes(Campaign $campaign): bool
    {
        $mailAdapter = $this->resolveMailAdapter($campaign->provider);

        $quota = $mailAdapter->getSendQuota();

        if (empty($quota)) {
            Log::error(
                'Failed to fetch quota from SES',
                [
                    'campaign_id' => $campaign->id,
                    'provider_id' => $campaign->provider->id,
                ]
            );

            return false;
        }

        $limit = Arr::get($quota, 'Max24HourSend');

        // -1 signifies an unlimited quota
        if ($limit === -1) {
            return true;
        }

        $sent = Arr::get($quota, 'SentLast24Hours');

        $remaining = (int) floor($limit - $sent);

        return $remaining > $campaign->unsent_count;
    }
}
