@extends('sendportal::layouts.app')

@section('title', __('Dashboard'))

@section('heading')
    {{ __('Dashboard') }}
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-accent">
                    <div class="card-header-inner">
                        {{ __('Subscriber Growth') }}
                    </div>
                </div>
                <div class="card-body">
                    <div style="width: 99%;">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header card-header-accent">
                    <div class="card-header-inner">
                        {{ __('Completed Campaigns') }}
                    </div>
                </div>
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
                        @forelse($completedCampaigns as $campaign)
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
                                <td>{{ $campaign->sent_count_formatted }}</td>
                                <td>{{ number_format($campaign->open_ratio * 100, 1) . '%' }}</td>
                                <td>{{ number_format($campaign->click_ratio * 100, 1) . '%' }}</td>
                                <td><span
                                        title="{{ $campaign->created_at }}">{{ $campaign->created_at->diffForHumans() }}</span>
                                </td>
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
                                        <button class="btn btn-light btn-sm btn-wide" type="button"
                                                id="dropdownMenuButton"
                                                data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true"
                                                aria-expanded="false">
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
                                    <p class="empty-table-text">{{ __('You have not completed any campaigns.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header card-header-accent">
                    <div class="card-header-inner">
                        {{ __('Recent Subscribers') }}
                    </div>
                </div>
                <div class="card-table table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Created') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentSubscribers as $subscriber)
                            <tr>
                                <td>
                                    <a href="{{ route('sendportal.subscribers.show', $subscriber->id) }}">
                                        {{ $subscriber->email }}
                                    </a>
                                </td>
                                <td>{{ $subscriber->full_name }}</td>
                                <td><span
                                        title="{{ $subscriber->created_at }}">{{ $subscriber->created_at->diffForHumans() }}</span>
                                </td>
                                <td>
                                    @if($subscriber->unsubscribed_at)
                                        <span class="badge badge-danger">{{ __('Unsubscribed') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('Subscribed') }}</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('sendportal.subscribers.edit', $subscriber->id) }}"
                                       class="btn btn-sm btn-light">{{ __('Edit') }}</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="empty-table-text">{{ __('No recent subscribers.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>

    <script>
        $(function () {
            var ctx = document.getElementById("growthChart");
            ctx.height = 300;
            var subscriberGrowthChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! $subscriberGrowthChartLabels !!},
                    datasets: [{
                        data: {!! $subscriberGrowthChartData !!},
                        label: "{{ __("Subscriber Count") }}",
                        backgroundColor: '#5D63FF',

                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                                suggestedMax: 10
                            }
                        }]
                    }
                }
            });
        });
    </script>
@endpush
