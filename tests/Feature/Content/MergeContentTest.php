<?php

declare(strict_types=1);

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Template;
use Sendportal\Base\Services\Content\MergeContentService;
use Tests\TestCase;

class MergeContentTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function campaign_content_can_be_merged()
    {
        // given
        $content = $this->faker->sentence;
        $message = $this->generateCampaignMessage($content);

        // when
        $mergedContent = $this->mergeContent($message);

        // then
        // NOTE(david): the string has to be formatted like this to match!
        $expectedHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p>' . $content . '</p></body></html>';

        self::assertEquals($expectedHtml, $mergedContent);
    }

    /** @test */
    public function it_can_handle_a_null_value_for_campaign_content()
    {
        $content = null;
        $message = $this->generateCampaignMessage(null, '<p>Hello this is some {{content}}</p>');

        $mergedContent = $this->mergeContent($message);

        $expectedHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p>Hello this is some </p></body></html>';

        self::assertEquals($expectedHtml, $mergedContent);
    }

    /** @test */
    public function the_unsubscribe_url_tag_is_replaced_with_a_valid_unsubscribe_link()
    {
        // given
        $message = $this->generateCampaignMessage('<a href="{{ unsubscribe_url }}">Unsubscribe Here</a>');

        // when
        $mergedContent = $this->mergeContent($message);

        // then
        $route = route('sendportal.subscriptions.unsubscribe', $message->hash);

        // NOTE(david): the string has to be formatted like this to match!
        $expectedHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p><a href="' . $route . '">Unsubscribe Here</a></p></body></html>';

        self::assertEquals($expectedHtml, $mergedContent);
    }

    /** @test */
    public function the_email_tag_is_replaced_with_the_subscriber_email()
    {
        // given
        $message = $this->generateCampaignMessage('Hi, {{ email }}');

        // when
        $mergedContent = $this->mergeContent($message);

        // then
        // NOTE(david): the string has to be formatted like this to match!
        $expectedHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p>Hi, ' . $message->recipient_email . '</p></body></html>';

        self::assertEquals($expectedHtml, $mergedContent);
    }

    /** @test */
    public function the_first_name_tag_is_replaced_with_the_subscriber_first_name()
    {
        // given
        $message = $this->generateCampaignMessage('Hi, {{ first_name }}');

        // when
        $mergedContent = $this->mergeContent($message);

        // then
        // NOTE(david): the string has to be formatted like this to match!
        $expectedHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p>Hi, ' . $message->subscriber->first_name . '</p></body></html>';

        self::assertEquals($expectedHtml, $mergedContent);
    }

    /** @test */
    public function the_last_name_tag_is_replaced_with_the_subscriber_last_name()
    {
        // given
        /** @var Workspace $workspace */
        $message = $this->generateCampaignMessage('Hi, {{ last_name }}');

        // when
        $mergedContent = $this->mergeContent($message);

        // then
        // NOTE(david): the string has to be formatted like this to match!
        $expectedHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p>Hi, ' . $message->subscriber->last_name . '</p></body></html>';

        self::assertEquals($expectedHtml, $mergedContent);
    }

    private function generateCampaignMessage(?string $campaignContent, ?string $templateContent = null): Message
    {
        /** @var Template $template */
        $template = Template::factory()->create([
            'content' => $templateContent ?? '<p>{{content}}</p>',
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'template_id' => $template->id,
            'content' => $campaignContent,
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        return Message::factory()->create([
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);
    }

    private function mergeContent(Message $message): string
    {
        /** @var MergeContentService $mergeContent */
        $mergeContent = app(MergeContentService::class);

        return $mergeContent->handle($message);
    }
}
