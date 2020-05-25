@extends('sendportal::layouts.app')

@section('title', __('Delete Campaign'))

@section('heading')
    @lang('Delete Campaign') - {{ $campaign->name }}
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
                {{ __('Confirm Delete') }}
            </div>
        </div>
        <div class="card-body">
            <p>
                {!! __('Are you sure that you want to delete the <b>:name</b> campaign?', ['name' => $campaign->name]) !!}
            </p>
            <form action="{{ route('sendportal.campaigns.destroy', $campaign->id) }}" method="post">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" value="{{ $campaign->id }}">
                <a href="{{ route('sendportal.campaigns.index') }}" class="btn btn-md btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-md btn-danger">{{ __('DELETE') }}</button>
            </form>
        </div>
    </div>

@endsection
