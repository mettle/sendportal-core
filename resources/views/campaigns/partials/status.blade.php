@if($campaign->draft)
    <span class="badge badge-light">{{ $campaign->status->name }}</span>
@elseif($campaign->queued)
    <span class="badge badge-warning">{{ $campaign->status->name }}</span>
@elseif($campaign->sending)
    <span class="badge badge-warning">{{ $campaign->status->name }}</span>
@elseif($campaign->sent)
    <span class="badge badge-success">{{ $campaign->status->name }}</span>
@elseif($campaign->cancelled)
    <span class="badge badge-danger">{{ $campaign->status->name }}</span>
@endif
