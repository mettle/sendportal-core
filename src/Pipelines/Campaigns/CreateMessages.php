<?php

namespace Sendportal\Base\Pipelines\Campaigns;

use App\Models\UserUnitHistory;
use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Jobs\AddSegmentSubscriberJob;
use Sendportal\Base\Models\Asset;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\SendportalCampaignSegment;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Tag;

class CreateMessages
{
    /**
     * Stores unique subscribers for this campaign
     *
     * @var array
     */
    protected $sentItems = [];

    /**
     * CreateMessages handler
     *
     * @param Campaign $campaign
     * @param $next
     * @return Campaign
     * @throws \Exception
     */
    public function handle(Campaign $campaign, $next)
    {
        if ($campaign->send_to_all) {
            $this->handleAllSubscribers($campaign);
        } else {
            $this->handleTags($campaign);
            $this->handleSegments($campaign);
        }

        return $next($campaign);
    }

    /**
     * Handle a campaign where all subscribers have been selected
     *
     * @param Campaign $campaign
     * @throws \Exception
     */
    protected function handleAllSubscribers(Campaign $campaign)
    {
        Subscriber::where('workspace_id', $campaign->workspace_id)
            ->whereNull('unsubscribed_at')
            ->chunkById(1000, function ($subscribers) use ($campaign) {
                $this->dispatchToSubscriber($campaign, $subscribers);
            }, 'id');
    }

    /**
     * Loop through each tag
     *
     * @param Campaign $campaign
     */
    protected function handleTags(Campaign $campaign)
    {
        foreach ($campaign->tags as $tag) {
            $this->handleTag($campaign, $tag);
        }
    }

    public function handleSegments(Campaign $campaign)
    {
        \Log::info(json_encode($campaign));
        foreach ($campaign->segments as $segment) {

            \Log::info(json_encode($segment));
            $this->handleSegment($campaign, $segment);
        }
    }


    /**
     * Handle each tag
     *
     * @param Campaign $campaign
     * @param Tag $tag
     *
     * @return void
     */
    protected function handleTag(Campaign $campaign, Tag $tag): void
    {
        \Log::info('- Handling Campaign Tag id='.$tag->id);

        $tag->subscribers()->whereNull('unsubscribed_at')->chunkById(1000, function ($subscribers) use ($campaign) {
            $this->dispatchToSubscriber($campaign, $subscribers);
        }, 'sendportal_subscribers.id');
    }

    public function handleSegment(Campaign $campaign, $segment)
    {
        \Log::info('- Handling Campaign Segment id='.$segment->id);

        $userIds = Asset::where('type', 'segment')->where('contract', $segment->id)->pluck('user_id')->toArray();

        if($campaign->type === 'recurrent')
        {
            AddSegmentSubscriberJob::dispatch($userIds, $campaign->workspace_id)->onQueue('events');
            Subscriber::whereIn('sc_user_id', $userIds)->where('workspace_id',$campaign->workspace_id)->whereNull('unsubscribed_at')->chunkById(1000, function ($subscribers) use ($campaign) {
                $this->dispatchToSubscriber($campaign, $subscribers);
            });
        }else {
            AddSegmentSubscriberJob::dispatchSync($userIds, $campaign->workspace_id);
            Subscriber::whereIn('sc_user_id', $userIds)->where('workspace_id', $campaign->workspace_id)->whereNull('unsubscribed_at')->chunkById(1000, function ($subscribers) use ($campaign) {
                $this->dispatchToSubscriber($campaign, $subscribers);
            });
        }
    }

    public function deductUnit($workspaceId,$note='')
    {
        return \DB::transaction(function()use($workspaceId,$note) {
            $totalUserUnit = \DB::table('user_units')->where('workspace_id',$workspaceId)->sharedLock()->first();

            if(empty($totalUserUnit))
            {
                return false;
            }

            if(($totalUserUnit['unit_balance'])<= 0) {
                return false;
            }
            $old_balance = $totalUserUnit['unit_balance'];
            $totalUserUnit->unit_balance = $new_balance = $totalUserUnit->unit_balance - 1;
            $totalUserUnit->save();
            $this->updateUserUnitHistory($workspaceId,'deduct',$totalUserUnit->id,$old_balance,1,$new_balance,$note);

            return true;

        });


    }

    public function updateUserUnitHistory($workspace_id,$action, $user_unit_id, $old_units, $amount, $new_units,$tags='')
    {

        $history =  \DB::table('user_unit_histories')->create([
            "user_unit_id" => $user_unit_id,
            "action" => $action,
            "old_unit_balance" => $old_units,
            "amount" => $amount,
            'tags'=>$tags,
            'workspace_id' => $workspace_id,
            "new_unit_balance" => $new_units
        ]);
        return $history;
    }

    /**
     * Dispatch the campaign to a given subscriber
     *
     * @param Campaign $campaign
     * @param $subscribers
     */
    protected function dispatchToSubscriber(Campaign $campaign, $subscribers)
    {
        \Log::info('- Number of subscribers in this chunk: ' . count($subscribers));

        foreach ($subscribers as $subscriber) {

            if (! $this->canSendToSubscriber($campaign->id, $subscriber->id)) {
                continue;
            }

            if(!$this->deductUnit($campaign->workspace_id,"Processed Campaign $campaign->id for subscriber $subscriber->id")) {
                break;
            }

            $this->dispatch($campaign, $subscriber);
        }
    }

    /**
     * Check if we can send to this subscriber
     * @todo check how this would impact on memory with 200k subscribers?
     *
     * @param int $campaignId
     * @param int $subscriberId
     *
     * @return bool
     */
    protected function canSendToSubscriber($campaignId, $subscriberId): bool
    {
        $key = $campaignId . '-' . $subscriberId;

        if (in_array($key, $this->sentItems, true)) {
            \Log::info('- Subscriber has already been sent a message campaign_id=' . $campaignId . ' subscriber_id=' . $subscriberId);

            return false;
        }

        $this->appendSentItem($key);

        return true;
    }

    /**
     * Append a value to the sentItems
     *
     * @param string $value
     * @return void
     */
    protected function appendSentItem(string $value): void
    {
        $this->sentItems[] = $value;
    }

    /**
     * Dispatch the message
     *
     * @param Campaign $campaign
     * @param Subscriber $subscriber
     */
    protected function dispatch(Campaign $campaign, Subscriber $subscriber): void
    {
        if ($campaign->save_as_draft) {
            $this->saveAsDraft($campaign, $subscriber);
        } else {
            $this->dispatchNow($campaign, $subscriber);
        }
    }

    /**
     * Dispatch a message now
     *
     * @param Campaign $campaign
     * @param Subscriber $subscriber
     * @return Message
     */
    protected function dispatchNow(Campaign $campaign, Subscriber $subscriber): Message
    {
        // If a message already exists, then we're going to assume that
        // it has already been dispatched. This makes the dispatch fault-tolerant
        // and prevent dispatching the same message to the same subscriber
        // more than once
        if ($message = $this->findMessage($campaign, $subscriber)) {
            \Log::info('Message has previously been created campaign=' . $campaign->id . ' subscriber=' . $subscriber->id);

            return $message;
        }

        // the message doesn't exist, so we'll create and dispatch
        \Log::info('Saving empty email message campaign=' . $campaign->id . ' subscriber=' . $subscriber->id);
        $attributes = [
            'workspace_id' => $campaign->workspace_id,
            'subscriber_id' => $subscriber->id,
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'recipient_email' => $subscriber->email,
            'subject' => $campaign->subject,
            'from_name' => $campaign->from_name,
            'from_email' => $campaign->from_email,
            'queued_at' => null,
            'sent_at' => null,
        ];

        $message = new Message($attributes);
        $message->save();

        event(new MessageDispatchEvent($message));

        return $message;
    }

    /**
     * @param Campaign $campaign
     * @param Subscriber $subscriber
     */
    protected function saveAsDraft(Campaign $campaign, Subscriber $subscriber)
    {
        \Log::info('Saving message as draft campaign=' . $campaign->id . ' subscriber=' . $subscriber->id);

        Message::firstOrCreate(
            [
                'workspace_id' => $campaign->workspace_id,
                'subscriber_id' => $subscriber->id,
                'source_type' => Campaign::class,
                'source_id' => $campaign->id,
            ],
            [
                'recipient_email' => $subscriber->email,
                'subject' => $campaign->subject,
                'from_name' => $campaign->from_name,
                'from_email' => $campaign->from_email,
                'queued_at' => now(),
                'sent_at' => null,
            ]
        );
    }

    protected function findMessage(Campaign $campaign, Subscriber $subscriber): ?Message
    {
        return Message::where('workspace_id', $campaign->workspace_id)
            ->where('subscriber_id', $subscriber->id)
            ->where('source_type', Campaign::class)
            ->where('source_id', $campaign->id)
            ->first();
    }
}
