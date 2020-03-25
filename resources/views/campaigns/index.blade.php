@extends('layouts.app')

@section('title', __('Campaigns'))

@section('heading')
    {{ __('Campaigns') }}
@endsection

@section('content')

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('campaigns.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Campaign') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Sent') }}</th>
                    <th>{{ __('Opened') }}</th>
                    <th>{{ __('Clicked') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($campaigns as $campaign)
                    <tr>
                        <td>
                            @if ($campaign->draft)
                                <a href="{{ route('campaigns.edit', $campaign->id) }}">{{ $campaign->name }}</a>
                            @elseif($campaign->sent)
                                <a href="{{ route('campaigns.reports.index', $campaign->id) }}">{{ $campaign->name }}</a>
                            @else
                                <a href="{{ route('campaigns.status', $campaign->id) }}">{{ $campaign->name }}</a>
                            @endif
                        </td>
                        <td>{{ formatValue($campaign->sent_count) }}</td>
                        <td>{{ formatRatio($campaign->open_ratio) }}</td>
                        <td>{{ formatRatio($campaign->click_ratio) }}</td>
                        <td><span title="{{ $campaign->created_at }}">{{ $campaign->created_at->diffForHumans() }}</span></td>
                        <td>
                            @if($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_DRAFT)
                                <span class="badge badge-light">{{ $campaign->status->name }}</span>
                            @elseif($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_QUEUED)
                                <span class="badge badge-warning">{{ $campaign->status->name }}</span>
                            @elseif($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_SENDING)
                                <span class="badge badge-warning">{{ $campaign->status->name }}</span>
                            @elseif($campaign->status_id === \Sendportal\Base\Models\CampaignStatus::STATUS_SENT)
                                <span class="badge badge-success">{{ $campaign->status->name }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @if ($campaign->draft)
                                        <a href="{{ route('campaigns.edit', $campaign->id) }}" class="dropdown-item">
                                            {{ __('Edit') }}
                                        </a>
                                    @else
                                        <a href="{{ route('campaigns.reports.index', $campaign->id) }}"  class="dropdown-item">
                                            {{ __('View Report') }}
                                        </a>
                                    @endif

                                    <a href="{{ route('campaigns.duplicate', $campaign->id) }}"  class="dropdown-item">
                                        {{ __('Duplicate') }}
                                    </a>


                                    @if ($campaign->draft)
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('campaigns.destroy.confirm', $campaign->id) }}"  class="dropdown-item">
                                            {{ __('Delete') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('You have not created any campaigns.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $campaigns])

@endsection
