@extends('sendportal::layouts.app')

@section('heading')
    {{ __('Providers') }}
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Edit Provider'))

        @slot('cardBody')
            {!! Form::open(['method' => 'post', 'class' => 'form-horizontal', 'route' => ['providers.update', $provider->id]]) !!}

            {!! Form::textField('name', __('Name'), $provider->name) !!}

            @include('providers.options.' . strtolower($providerType->name), ['settings' => $provider->settings])

            {!! Form::submitButton(__('Update')) !!}
            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
