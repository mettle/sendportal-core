@extends('sendportal::layouts.app')

@section('title', __('New Segment'))

@section('heading')
    {{ __('Segments') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Segment'))

        @slot('cardBody')
            {!! Form::open(['route' => ['sendportal.segments.store'], 'class' => 'form-horizontal']) !!}

            @include('sendportal::segments.partials.form')

            {!! Form::submitButton(__('Save')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
