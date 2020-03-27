@extends('sendportal::layouts.app')

@section('title', __("Edit Subscriber") . " : {$subscriber->full_name}")

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Edit Subscriber'))

        @slot('cardBody')
            {!! Form::model($subscriber, ['method' => 'put', 'class' => 'form-horizontal', 'route' => ['sendportal.subscribers.update', $subscriber->id]]) !!}

            @include('sendportal::subscribers.partials.form')

            {!! Form::submitButton(__('Save')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
