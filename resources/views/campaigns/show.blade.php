@extends('common.template')

@section('heading')
    {{ __('Campaign') }}: {{ $campaign->name }}
@stop

@section('content')

    @if ($campaign->content ?? false)
        <a href="{{ route('campaigns.preview', $campaign->id) }}">
            {{ __('Confirm and Send Campaign') }}
        </a>
    @else
        <ul>
            <li><a href="{{ route('campaigns.edit', $campaign->id) }}">{{ __('Edit Campaign') }}</a></li>
            <li><a href="{{ route('campaigns.create', ['id' => $campaign->id]) }}">{{ __('Create Email') }}</a></li>
        </ul>
    @endif

@stop
