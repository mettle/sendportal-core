<?php

declare(strict_types=1);

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Services\Content\MergeSubjectService;
use Tests\TestCase;

class MergeSubjectTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function the_email_tag_is_replaced_with_the_subscriber_email()
    {
        // given
        $subject = 'Hi, {{email}}';
        $message = $this->generateCampaignMessage($subject, 'foo@bar.com', 'foo', 'bar');

        // when
        $mergedSubject = $this->mergeSubject($message);

        // then
        self::assertEquals('Hi, foo@bar.com', $mergedSubject);
    }

    /** @test */
    public function the_first_name_tag_is_replaced_with_the_subscriber_first_name()
    {
        // given
        $subject = 'Hi, {{first_name}}';
        $message = $this->generateCampaignMessage($subject, 'foo@bar.com', 'foo', 'bar');

        // when
        $mergedSubject = $this->mergeSubject($message);

        // then
        self::assertEquals('Hi, foo', $mergedSubject);
    }

    /** @test */
    public function the_last_name_tag_is_replaced_with_the_subscriber_last_name()
    {
        // given
        $subject = 'Hi, {{last_name}}';
        $message = $this->generateCampaignMessage($subject, 'foo@bar.com', 'foo', 'bar');

        // when
        $mergedSubject = $this->mergeSubject($message);

        // then
        self::assertEquals('Hi, bar', $mergedSubject);
    }

    /** @test */
    public function multiple_different_tags_are_replaced()
    {
        // given
        $subject = 'Hi, {{first_name}} {{ last_name }} ({{email }})';
        $message = $this->generateCampaignMessage($subject, 'foo@bar.com', 'foo', 'bar');

        // when
        $mergedSubject = $this->mergeSubject($message);

        // then
        self::assertEquals('Hi, foo bar (foo@bar.com)', $mergedSubject);
    }

    private function generateCampaignMessage(
        string $campaignSubject,
        string $email,
        string $firstName,
        string $lastName
    ): Message {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'content' => '<p>Content</p>',
            'subject' => $campaignSubject,
            'workspace_id' => Sendportal::currentWorkspaceId()
        ]);

        /** @var Subscriber $subscriber */
        $subscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        return Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => $subscriber->id,
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'subject' => $campaignSubject,
            'recipient_email' => $email,
        ]);
    }

    private function mergeSubject(Message $message): string
    {
        /** @var MergeSubjectService $mergeSubject */
        $mergeSubject = app(MergeSubjectService::class);

        return $mergeSubject->handle($message);
    }
}
