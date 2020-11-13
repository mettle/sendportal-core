@extends('sendportal::layouts.app')

@section('title', $campaign->name)

@section('heading')
    {{ $campaign->name }}
@endsection

@section('content')

    @include('sendportal::campaigns.reports.partials.nav')

    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-md-0 mb-3">
            <a href="{{ route('sendportal.campaigns.reports.recipients', $campaign->id) }}"
               class="text-decoration-none text-reset">
                <div class="widget flex-row align-items-center align-items-stretch">
                    <div class="col-8 py-4 rounded-right">
                        <div class="h2 m-0">{{ $campaignStats['counts']['sent'] }}</div>
                        <div class="text-uppercase">{{ __('Emails Sent') }}</div>
                    </div>
                    <div class="col-4 d-flex align-items-center justify-content-center rounded-left">
                        <em class="far fa-paper-plane fa-2x color-gray-400"></em>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-md-0 mb-3">
            <a href="{{ route('sendportal.campaigns.reports.opens', $campaign->id) }}"
               class="text-decoration-none text-reset">
                <div class="widget flex-row align-items-center align-items-stretch">
                    <div class="col-8 py-4 rounded-right">
                        <div class="h2 m-0">{{ round($campaignStats['ratios']['open'] * 100, 1) }}%</div>
                        <div class="text-uppercase">{{ __('Unique Opens') }}</div>
                    </div>
                    <div class="col-4 d-flex align-items-center justify-content-center rounded-left">
                        <em class="far fa-envelope-open fa-2x color-gray-400"></em>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-md-0 mb-3">
            <a href="{{ route('sendportal.campaigns.reports.clicks', $campaign->id) }}"
               class="text-decoration-none text-reset">
                <div class="widget flex-row align-items-center align-items-stretch">
                    <div class="col-8 py-4 rounded-right">
                        <div class="h2 m-0">{{ round($campaignStats['ratios']['click'] * 100, 1) }}%</div>
                        <div class="text-uppercase">{{ __('Click Rate') }}</div>
                    </div>
                    <div class="col-4 d-flex align-items-center justify-content-center rounded-left">
                        <em class="far fa-hand-pointer fa-2x color-gray-400"></em>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-md-0 mb-3">
            <a href="{{ route('sendportal.campaigns.reports.bounces', $campaign->id) }}"
               class="text-decoration-none text-reset">

                <div class="widget flex-row align-items-center align-items-stretch">
                    <div class="col-8 py-4 rounded-right">
                        <div class="h2 m-0">{{ round($campaignStats['ratios']['bounce'] * 100, 1) }}%</div>
                        <div class="text-uppercase">{{ __('Bounce Rate') }}</div>
                    </div>
                    <div class="col-4 d-flex align-items-center justify-content-center rounded-left">
                        <em class="fas fa-exchange-alt fa-2x color-gray-400"></em>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-accent">
            <div class="card-header-inner">
                {{ __('Unique Opens') }}

                <div class="float-right">
                    <select name="interval" id="interval" class="form-control">
                        @foreach([
                            12 => __("12 Hours"),
                            24 => __("24 Hours"),
                            48 => __("48 Hours")
                        ] as $key => $label)
                            <option value="{{ $key }}" {{ request()->get('interval', 24) == $key ? 'selected' : null }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div style="width: 99%;">
                <canvas id="opensChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-accent">
            <div class="card-header-inner">
                {{ __('Top Clicked Links') }}
            </div>
        </div>
        <div class="card-table table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <td><b>{{ __('URL') }}</b></td>
                    <td class="text-right"><b>{{ __('Click Count') }}</b></td>
                </tr>
                @forelse($campaignUrls as $campaignUrl)
                    <tr class="campaign-link">
                        <td>{{ $campaignUrl->url }}</td>
                        <td class="text-right">{{ $campaignUrl->click_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('No links have been clicked.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection


@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>

    <script>
        $(function () {
            var ctx = document.getElementById("opensChart");
            ctx.height = 300;
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! $chartLabels !!},
                    datasets: [{
                        data: {!! $chartData !!},
                        label: "{{ __("Opens") }}",
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

            $("select[name=interval]").on("change", function () {
                var url = new URL(window.location.href);
                url.searchParams.set('interval', $(this).val());
                window.location.href = url.href;
            })
        });
    </script>
@endpush
