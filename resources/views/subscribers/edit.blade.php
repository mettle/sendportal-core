@extends('sendportal::layouts.app')

@section('title', __("Edit Subscriber") . " : {$subscriber->full_name}")

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Edit Subscriber'))

        @slot('cardBody')
            {!! Form::model($subscriber, ['method' => 'put', 'class' => 'form-horizontal', 'route' => ['subscribers.update', $subscriber->id]]) !!}

            @include('subscribers.partials.form')

            {!! Form::submitButton(__('Save')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
