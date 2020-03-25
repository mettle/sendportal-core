@extends('sendportal::layouts.app')

@section('title', __('Campaign Status'))

@section('heading')
    {{ __('Campaign Status') }}
@stop

@section('content')

{{ __('Your campaign is currently') }} {{ $campaign->status->name }}

<div class="row text-center">
    <div class="col-sm-6">
        @include('sendportal::svgs.undraw_in_progress')
    </div>
</div>

@stop
