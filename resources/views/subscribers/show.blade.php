@extends('sendportal::layouts.app')

@section('title', __("Subscriber") . " : {$subscriber->full_name}")

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.actions')
        @slot('right')
            <a class="btn btn-light btn-md btn-flat" href="{{ route('sendportal.subscribers.edit', $subscriber->id) }}">
                <i class="fa fa-edit mr-1"></i> {{ __('Edit Subscriber') }}
            </a>
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-table">
                    <div class="table">
                        <table class="table">
                            <tr>
                                <td><b>{{ __('Email') }}</b></td>
                                <td>{{ $subscriber->email }}</td>
                            </tr>
                            <tr>
                                <td><b>{{ __('First Name') }}</b></td>
                                <td>{{ $subscriber->first_name }}</td>
                            </tr>
                            <tr>
                                <td><b>{{ __('Last Name') }}</b></td>
                                <td>{{ $subscriber->last_name }}</td>
                            </tr>
                            <tr>
                                <td><b>{{ __('Status') }}</b></td>
                                <td>
                                    @if($subscriber->unsubscribed_at)
                                        <span class="badge badge-danger">{{ __('Unsubscribed') }}</span>
                                        <span class="text-muted">{{ \Sendportal\Base\Models\UnsubscribeEventType::findById($subscriber->unsubscribe_event_id) }}
                                            on {{ \Sendportal\Base\Facades\Helper::displayDate($subscriber->unsubscribed_at)->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="badge badge-success">{{ __('Subscribed') }}</span> <span class="text-muted">{{ \Sendportal\Base\Facades\Helper::displayDate($subscriber->created_at) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><b>{{ __('Tags') }}</b></td>
                                <td>
                                    @foreach($subscriber->tags as $tag)
                                        <span class="badge badge-light">{{ $tag->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            {{ __('Messages') }}
        </div>
        <div class="card-table">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Source') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($subscriber->messages as $message)
                    <tr class="campaign-link">
                        <td>
                            {{ $message->sent_at ?? $message->created_at }}
                        </td>
                        <td>
                            {{ $message->subject }}
                        </td>
                        <td>
                            @if($message->isCampaign())
                                <i class="fas fa-envelope color-gray-300"></i>
                                <a href="{{ route('sendportal.campaigns.reports.index', $message->source_id) }}">
                                    {{ $message->source->name }}
                                </a>
                            @elseif(\Sendportal\Base\Facades\Helper::isPro() && $message->isAutomation())
                                <i class="fas fa-sync-alt color-gray-300"></i>
                                <a href="{{ route('sendportal.automations.show', $message->source->automation_step->automation_id) }}">
                                    {{ $message->source->automation_step->automation->name }}
                                </a>
                            @endif
                        </td>
                        <td>
                            @include('sendportal::messages.partials.status-row')
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('No Messages') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop
