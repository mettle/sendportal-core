<?php
/**
 * Criado por Maizer Aly de O. Gomes para sendportal-core.
 * Email: maizer.gomes@gmail.com / maizer.gomes@ekutivasolutions / maizer.gomes@outlook.com
 * Usuário: maizerg
 * Data: 6/10/20
 * Hora: 8:11 PM
 */

namespace Sendportal\Base\Adapters;


use ElasticEmailClient\ApiConfiguration;
use ElasticEmailClient\ElasticClient;
use Illuminate\Support\Arr;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;

class ElasticMailAdapter extends BaseMailAdapter
{

    protected $url = 'https://api.elasticemail.com/v2/';
    /**
     * @var \ElasticEmailClient\ElasticClient
     */
    protected $client;

    /**
     * @inheritDoc
     */
    public function send(string $fromEmail, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): ?string
    {
        $result = $this->resolveClient()->Email->Send(
            $subject,
            $fromEmail,
            //TODO Need to get fromName from the campaign
            null,
            $fromEmail,
            null,
            $fromEmail,
            null,
            null,
            null,
            [$toEmail],
            [],
            [],
            [],
            [],
            [],
            null,
            null,
            null,
            $content,
            null,
            'utf-8',
            null,
            null,
            null,
            null,
            [],
            [],
            null,
            [],
            null,
            null,
            null,
            []
            //TODO ElasticEmail API rejects request when tracking options are set. Maybe because my account is trial.
//            $trackingOptions->isOpenTracking(),
//            $trackingOptions->isClickTracking()
        );

        return $this->resolveMessageId($result);
    }

    protected function resolveClient(): ElasticClient
    {
        if ($this->client) {
            return $this->client;
        }

        $configuration = new ApiConfiguration([
            'apiUrl' => $this->url,
            'apiKey' => Arr::get($this->config, 'key'),
        ]);

        $this->client = new ElasticClient($configuration);

        return $this->client;
    }

    protected function resolveMessageId($result): string
    {
        return $result->messageid;
    }
}
