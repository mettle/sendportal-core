<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Content;

use Exception;
use Sendportal\Automations\Models\AutomationSchedule;
use Sendportal\Automations\Repositories\AutomationScheduleRepository;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\CampaignTenantRepository;
use Sendportal\Base\Traits\NormalizeTags;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class MergeContent
{
    use NormalizeTags;

    /** @var CampaignTenantRepository */
    protected $campaignRepo;

    /** @var CssToInlineStyles */
    protected $cssProcessor;

    public function __construct(
        CampaignTenantRepository $campaignRepo,
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
        if ($message->isCampaign()) {
            $mergedContent = $this->mergeCampaignContent($message);
        } elseif ($message->isAutomation()) {
            $mergedContent = $this->mergeAutomationContent($message);
        } else {
            throw new Exception('Invalid message source type for message id=' . $message->id);
        }

        return $this->mergeTags($mergedContent, $message);
    }

    /**
     * @throws Exception
     */
    protected function mergeCampaignContent(Message $message): string
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepo->find($message->workspace_id, $message->source_id, ['template']);

        if (!$campaign) {
            throw new Exception('Unable to resolve campaign step for message id= ' . $message->id);
        }

        return $campaign->template
            ? $this->mergeContent($campaign->content, $campaign->template->content)
            : $campaign->content;
    }

    /**
     * @throws Exception
     */
    protected function mergeAutomationContent(Message $message): string
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

        return $this->mergeContent($content, $template->content);
    }

    protected function mergeContent(string $customContent, string $templateContent): string
    {
        return str_ireplace(['{{content}}', '{{ content }}'], $customContent, $templateContent);
    }

    protected function mergeTags(string $content, Message $message): string
    {
        $content = $this->compileTags($content);

        $content = $this->mergeSubscriberTags($content, $message);
        $content = $this->mergeUnsubscribeLink($content, $message);
        $content = $this->mergeWebviewLink($content, $message);

        return $content;
    }

    protected function compileTags(string $content): string
    {
        $tags = [
            'email',
            'first_name',
            'last_name',
            'unsubscribe_url',
            'webview_url'
        ];

        // NOTE: regex doesn't seem to work here - I think it may be due to all the tags and inverted commas in html?
        foreach ($tags as $tag) {
            $content = $this->normalizeTags($content, $tag);
        }

        return $content;
    }

    protected function mergeSubscriberTags(string $content, Message $message): string
    {
        $tags = [
            'email' => $message->recipient_email,
            'first_name' => $message->subscriber ? $message->subscriber->first_name : '',
            'last_name' => $message->subscriber ? $message->subscriber->last_name : '',
        ];

        foreach ($tags as $key => $replace) {
            $content = str_ireplace('{{' . $key . '}}', $replace, $content);
        }

        return $content;
    }

    protected function mergeUnsubscribeLink(string $content, Message $message): string
    {
        $unsubscribeLink = $this->generateUnsubscribeLink($message);

        return str_ireplace(['{{ unsubscribe_url }}', '{{unsubscribe_url}}'], $unsubscribeLink, $content);
    }

    protected function generateUnsubscribeLink(Message $message): string
    {
        return route('sendportal.subscriptions.unsubscribe', $message->hash);
    }

    protected function mergeWebviewLink(string $content, Message $message): string
    {
        $webviewLink = $this->generateWebviewLink($message);

        return str_ireplace('{{webview_url}}', $webviewLink, $content);
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
