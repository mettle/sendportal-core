@extends('sendportal::layouts.app')

@section('title', __('Campaign Design'))

@section('heading')
    {{ __('Campaign Design') }}
@stop

@section('content')

    {!! Form::model($campaign, array('method' => 'put', 'route' => array('campaigns.content.update', $campaign->id))) !!}

    @include('sendportal::templates.partials.editor')

    <br>

    <a href="{{ route('sendportal.campaigns.template', $campaign->id) }}" class="btn btn-link"><i
            class="fa fa-arrow-left"></i> {{ __('Back') }}</a>

    <button class="btn btn-primary" type="submit">{{ __('Save and continue') }}</button>

    {!! Form::close() !!}
@stop
