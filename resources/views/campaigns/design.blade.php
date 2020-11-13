@extends('sendportal::layouts.app')

@section('title', __('Campaign Design'))

@section('heading')
    {{ __('Campaign Design') }}
@stop

@section('content')

    <form action="{{ route('campaigns.content.update', $campaign->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('sendportal::templates.partials.editor')

        <br>

        <a href="{{ route('sendportal.campaigns.template', $campaign->id) }}" class="btn btn-link"><i
                class="fa fa-arrow-left"></i> {{ __('Back') }}</a>

        <button class="btn btn-primary" type="submit">{{ __('Save and continue') }}</button>
    </form>
@stop
