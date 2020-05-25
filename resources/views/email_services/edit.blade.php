@extends('sendportal::layouts.app')

@section('heading')
    {{ __('Email Services') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Edit Email Service'))

        @slot('cardBody')
            {!! Form::open(['method' => 'PUT', 'class' => 'form-horizontal', 'route' => ['sendportal.email_services.update', $emailService->id]]) !!}

            {!! Form::textField('name', __('Name'), $emailService->name) !!}

            @include('sendportal::email_services.options.' . strtolower($emailServiceType->name), ['settings' => $emailService->settings])

            {!! Form::submitButton(__('Update')) !!}
            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
