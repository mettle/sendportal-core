@extends('sendportal::layouts.app')

@section('title', __('Cancel Campaign'))

@section('heading')
    @lang('Cancel Campaign') - {{ $campaign->name }}
@endsection

@section('content')

    @component('sendportal::layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.campaigns.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('Create Campaign') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-header card-header-accent">
            <div class="card-header-inner">
                {{ __('Confirm Cancellation') }}
            </div>
        </div>
        <div class="card-body">
            <p>
                {!! __('Are you sure that you want to cancel the <b>:name</b> campaign?', ['name' => $campaign->name]) !!}
            </p>

            <p>
                @if($campaign->save_as_draft)
                    {!! __('All draft messages will be permanently deleted.') !!}
                @else
                    {!! __('Messages already dispatched will not be deleted. Unsent messages will not be dispatched.') !!}
                @endif
            </p>

            <form action="{{ route('sendportal.campaigns.cancel', $campaign->id) }}" method="post">
                @csrf
                <a href="{{ route('sendportal.campaigns.index') }}" class="btn btn-md btn-light">{{ __('Go Back') }}</a>
                <button type="submit" class="btn btn-md btn-danger">{{ __('CANCEL') }}</button>
            </form>
        </div>
    </div>

@endsection
