@if ($message->bounced_at)
    <div class="badge badge-danger">{{ __('Bounced') }}</div>
@elseif ($message->unsubscribed_at)
    <div class="badge badge-danger">{{ __('Unsubscribed') }}</div>
@elseif ($message->clicked_at)
    <div class="badge badge-success">{{ __('Clicked') }}</div>
@elseif ($message->opened_at)
    <div class="badge badge-success">{{ __('Opened') }}</div>
@elseif ($message->delivered_at)
    <div class="badge badge-info">{{ __('Delivered') }}</div>
@elseif ($message->sent_at)
    <div class="badge badge-light">{{ __('Sent') }}</div>
@else
    <div class="badge badge-light">{{ __('Draft') }}</div>
@endif
