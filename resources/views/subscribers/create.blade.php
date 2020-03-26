@extends('sendportal::layouts.app')

@section('title', __('New Subscriber'))

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Create Subscriber'))

        @slot('cardBody')
            {!! Form::open(['route' => ['subscribers.store'], 'class' => 'form-horizontal']) !!}

            @include('subscribers.partials.form')

            {!! Form::submitButton(__('Save')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
