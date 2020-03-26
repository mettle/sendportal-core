@extends('sendportal::layouts.app')

@section('title', __('New Subscriber'))

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Subscriber'))

        @slot('cardBody')
            {!! Form::open(['route' => ['sendportal.subscribers.store'], 'class' => 'form-horizontal']) !!}

            @include('sendportal::subscribers.partials.form')

            {!! Form::submitButton(__('Save')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
