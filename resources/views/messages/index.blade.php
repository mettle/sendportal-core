@extends('layouts.app')

@section('title', __('Messages'))

@section('heading', __('Messages'))

@section('content')

    @include('messages.partials.nav')

    @component('layouts.partials.actions')
        @slot('left')
            <form action="{{ route('messages.index') }}" method="GET" class="form-inline">
                <div class="mr-2">
                    <input type="text" class="form-control" placeholder="Search..." name="search" value="{{ request('search') }}">
                </div>

                @if(request()->route()->named('messages.index'))
                    <div class="mr-2">
                        <select name="status" class="form-control">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>{{ __('Sent') }}</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                            <option value="opened" {{ request('status') == 'opened' ? 'selected' : '' }}>{{ __('Opened') }}</option>
                            <option value="clicked" {{ request('status') == 'clicked' ? 'selected' : '' }}>{{ __('Clicked') }}</option>
                            <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>{{ __('Unsubscribed') }}</option>
                            <option value="bounced" {{ request('status') == 'bounced' ? 'selected' : '' }}>{{ __('Bounced') }}</option>
                        </select>
                    </div>
                @endif

                <button type="submit" class="btn btn-light">{{ __('Search') }}</button>

                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('messages.index') }}" class="btn btn-light">{{ __('Clear') }}</a>
                @endif
            </form>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Recipient') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>
                                {{ $message->sent_at ?? $message->created_at }}
                            </td>
                            <td>{{ $message->subject }}</td>
                            <td>
                                @if($message->source_type == \Sendportal\Base\Models\Campaign::class)
                                    <i class="fas fa-envelope fc-gray-300"></i>
                                    <a href="{{ route('campaigns.reports.index', $message->source_id) }}">
                                        {{ $message->source->name }}
                                    </a>
                                @elseif(automationsEnable() && $message->source_type == \Sendportal\Automations\Models\AutomationSchedule::class)
                                    <i class="fas fa-sync-alt fc-gray-300"></i>
                                    <a href="{{ route('automations.show', $message->source->automation_step->automation_id) }}">
                                        {{ $message->source->automation_step->automation->name }}
                                    </a>
                                @endif
                            </td>
                            <td><a href="{{ route('subscribers.show', $message->subscriber_id) }}">{{ $message->recipient_email }}</a></td>
                            <td>
                                @include('messages.partials.status-row')
                            </td>
                            <td>
                                @if ( ! $message->sent_at)
                                    <form action="{{ route('messages.send') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $message->id }}">
                                        <a href="{{ route('messages.show', $message->id) }}" class="btn btn-xs btn-light">{{ __('Preview') }}</a>
                                        <button type="submit" class="btn btn-xs btn-light">{{ __('Send now') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <p class="empty-table-text">{{ __('There are no messages') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $messages])

@endsection
