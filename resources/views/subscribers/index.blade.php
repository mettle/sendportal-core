@extends('layouts.app')

@section('title', __('Subscribers'))

@section('heading')
    {{ __('Subscribers') }}
@endsection

@section('content')

    @component('layouts.partials.actions')

        @slot('left')
            <form action="{{ route('subscribers.index') }}" method="GET" class="form-inline mb-3 mb-md-0">
                <input class="form-control form-control-sm" name="name" type="text" value="{{ request('name') }}"
                       placeholder="{{ __('Search...') }}">

                <div class="mr-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                        <option value="subscribed" {{ request('status') == 'subscribed' ? 'selected' : '' }}>{{ __('Subscribed') }}</option>
                        <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>{{ __('Unsubscribed') }}</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-light btn-md">{{ __('Search') }}</button>

                @if(request()->anyFilled(['name', 'status']))
                    <a href="{{ route('subscribers.index') }}" class="btn btn-md btn-light">{{ __('Clear') }}</a>
                @endif
            </form>
        @endslot

        @slot('right')
            <div class="btn-group mr-2">
                <button class="btn btn-md btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="{{ route('subscribers.import') }}" class="dropdown-item">
                        <i class="fa fa-upload mr-2"></i> {{ __('Import Subscribers') }}
                    </a>
                    <a href="{{ route('subscribers.export') }}" class="dropdown-item">
                        <i class="fa fa-download mr-2"></i> {{ __('Export Subscribers') }}
                    </a>

                </div>
            </div>
            <a class="btn btn-light btn-md mr-2" href="{{ route('segments.index') }}">
                {{ __('Segments') }}
            </a>
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('subscribers.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Subscriber') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
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
                @forelse($subscribers as $subscriber)
                    <tr>
                        <td>
                            <a href="{{ route('subscribers.show', $subscriber->id) }}">
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
                        <td><a href="{{ route('subscribers.edit', $subscriber->id) }}" class="btn btn-sm btn-light">{{ __('Edit') }}</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('No Subscribers Found') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $subscribers])

@endsection
