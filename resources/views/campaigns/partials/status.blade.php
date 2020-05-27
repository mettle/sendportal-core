@if($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_DRAFT)
    <span class="badge badge-light">{{ $campaign->status->name }}</span>
@elseif($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_QUEUED)
    <span class="badge badge-warning">{{ $campaign->status->name }}</span>
@elseif($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_SENDING)
    <span class="badge badge-warning">{{ $campaign->status->name }}</span>
@elseif($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_SENT)
    <span class="badge badge-success">{{ $campaign->status->name }}</span>
@endif
