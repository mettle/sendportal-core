@extends('sendportal::layouts.app')

@section('title', $campaign->name)

@section('heading', $campaign->name)

@section('content')

    @include('sendportal::campaigns.reports.partials.nav')

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Subscriber') }}</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Bounced') }}</th>
                </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td><a href="{{ route('sendportal.subscribers.show', $message->subscriber_id) }}">{{ $message->recipient_email }}</a></td>
                            <td>
                                {{ $message->subject }}

                                @foreach($message->failures as $failure)
                                    <div class="mt-2 color-gray-500">
                                        {{ $failure->failed_at }}&nbsp;:&nbsp;{{ $failure->severity }}&nbsp;-&nbsp;{{ $failure->description }}
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ \Sendportal\Base\Facades\Helper::displayDate($message->bounced_at) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <p class="empty-table-text">{{ __('There are no subscribers') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('sendportal::layouts.partials.pagination', ['records' => $messages])

@endsection
