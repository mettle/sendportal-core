<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Content;

use Exception;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Traits\NormalizeTags;
use Sendportal\Pro\Repositories\AutomationScheduleRepository;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Illuminate\Support\Facades\Blade;

class MergeContentService
{
    use NormalizeTags;

    /** @var CampaignTenantRepositoryInterface */
    protected $campaignRepo;

    /** @var CssToInlineStyles */
    protected $cssProcessor;

    public function __construct(
        CampaignTenantRepositoryInterface $campaignRepo,
        CssToInlineStyles $cssProcessor
    ) {
        $this->campaignRepo = $campaignRepo;
        $this->cssProcessor = $cssProcessor;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): string
    {
        return $this->inlineStyles($this->resolveContent($message));
    }

    /**
     * @throws Exception
     */
    protected function resolveContent(Message $message): string
    {
        $tags = $this->generateTags($message);

        if ($message->isCampaign()) {
            $mergedContent = $this->mergeCampaignContent($message, $tags);
        } elseif ($message->isAutomation()) {
            $mergedContent = $this->mergeAutomationContent($message, $tags);
        } else {
            throw new Exception('Invalid message source type for message id=' . $message->id);
        }

        return $mergedContent;
    }

    /**
     * @throws Exception
     */
    protected function mergeCampaignContent(Message $message, $tags): string
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepo->find($message->workspace_id, $message->source_id, ['template']);

        if (!$campaign) {
            throw new Exception('Unable to resolve campaign step for message id= ' . $message->id);
        }

        return $campaign->template
            ? $this->mergeContent($campaign->content, $campaign->template->content, $tags)
            : $this->mergeContent($campaign->content, null, $tags);
    }

    /**
     * @throws Exception
     */
    protected function mergeAutomationContent(Message $message, $tags): string
    {
        if (!$schedule = app(AutomationScheduleRepository::class)->find($message->source_id, ['automation_step'])) {
            throw new Exception('Unable to resolve automation step for message id=' . $message->id);
        }

        if (!$content = $schedule->automation_step->content) {
            throw new Exception('Unable to resolve content for automation step id=' . $schedule->automation_step_id);
        }

        if (!$template = $schedule->automation_step->template) {
            throw new Exception('Unable to resolve template for automation step id=' . $schedule->automation_step_id);
        }

        return $this->mergeContent($content, $template->content, $tags);
    }

    protected function mergeContent(?string $customContent, string $templateContent, $tags): string
    {
        $customContent = $customContent ?: '';

        $tags['content'] = $customContent;

        return Blade::render(
            $templateContent ?? $customContent,
            $tags
        );
    }

    protected function generateTags(Message $message): array
    {
        $tags = [
            'email' => $message->recipient_email,
            'first_name' => optional($message->subscriber)->first_name ?? '',
            'last_name' => optional($message->subscriber)->last_name ?? '',
            'unsubscribe_url' => $this->generateUnsubscribeLink($message),
            'webview_url' => $this->generateWebviewLink($message)
        ];

        return $tags;
    }

    protected function generateUnsubscribeLink(Message $message): string
    {
        return route('sendportal.subscriptions.unsubscribe', $message->hash);
    }

    protected function generateWebviewLink(Message $message): string
    {
        return route('sendportal.webview.show', $message->hash);
    }

    protected function inlineStyles(string $content): string
    {
        return $this->cssProcessor->convert($content);
    }
}
