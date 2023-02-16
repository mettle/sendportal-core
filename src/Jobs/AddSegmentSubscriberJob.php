<?php

namespace Sendportal\Base\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sendportal\Base\Models\SocialconnectorUser;
use Sendportal\Base\Models\Subscriber;

class AddSegmentSubscriberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userIds, $workspaceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userIds, $workspaceId)
    {
        $this->userIds = $userIds;
        $this->workspaceId = $workspaceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $users = $this->userIds;

        foreach ($users as $user)
        {
            $subscriber = Subscriber::where('sc_user_id', $this->userIds)->where('workspace_id', $this->workspaceId)->first();
            if(empty($subscriber)) {
                $scUser = SocialconnectorUser::where('id', $user)->first();
                if(!empty($scUser)) {
                    $leadTag = updateOrCreate(
                        ['workspace_id' => $this->workspaceId, 'name' => 'lead'],
                        ['workspace_id' => $this->workspaceId, 'name' => 'lead']
                    );

                    $subscriber = new Subscriber();
                    $subscriber->first_name = explode(' ', $scUser->name)[0] ?? null;
                    $subscriber->last_name = explode(' ', $scUser->name)[1] ?? null;
                    $subscriber->workspace_id = $this->workspaceId;
                    $subscriber->email = $scUser->email;
                    $subscriber->sc_user_id = $scUser->id;
                    $subscriber->save();

                    $subscriber->tags()->attach($leadTag);

                }

            }

        }

    }
}
