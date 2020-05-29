@extends('sendportal::layouts.app')

@section('title', __('Campaigns'))

@section('heading')
    {{ __('Campaigns') }}
@endsection

@section('content')

    @component('sendportal::layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.campaigns.create') }}">
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
                                <a href="{{ route('sendportal.campaigns.edit', $campaign->id) }}">{{ $campaign->name }}</a>
                            @elseif($campaign->sent)
                                <a href="{{ route('sendportal.campaigns.reports.index', $campaign->id) }}">{{ $campaign->name }}</a>
                            @else
                                <a href="{{ route('sendportal.campaigns.status', $campaign->id) }}">{{ $campaign->name }}</a>
                            @endif
                        </td>
                        <td>{{ $campaignStats[$campaign->id]['counts']['sent'] }}</td>
                        <td>{{ number_format($campaignStats[$campaign->id]['ratios']['open'] * 100, 1) . '%' }}</td>
                        <td>{{ number_format($campaignStats[$campaign->id]['ratios']['click'] * 100, 1) . '%' }}</td>
                        <td><span title="{{ $campaign->created_at }}">{{ $campaign->created_at->diffForHumans() }}</span></td>
                        <td>
                            @include('sendportal::campaigns.partials.status')
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @if ($campaign->draft)
                                        <a href="{{ route('sendportal.campaigns.edit', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('Edit') }}
                                        </a>
                                    @else
                                        <a href="{{ route('sendportal.campaigns.reports.index', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('View Report') }}
                                        </a>
                                    @endif

                                    <a href="{{ route('sendportal.campaigns.duplicate', $campaign->id) }}"
                                       class="dropdown-item">
                                        {{ __('Duplicate') }}
                                    </a>


                                    @if ($campaign->draft)
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('sendportal.campaigns.destroy.confirm', $campaign->id) }}"
                                           class="dropdown-item">
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

    @include('sendportal::layouts.partials.pagination', ['records' => $campaigns])

@endsection
