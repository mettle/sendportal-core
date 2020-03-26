@extends('sendportal::layouts.app')

@section('title', __("Edit Segment"))

@section('heading')
    {{ __('Segments') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Edit Segment'))

        @slot('cardBody')
            {!! Form::model($segment, ['method' => 'put', 'class' => 'form-horizontal', 'route' => ['sendportal.segments.update', $segment->id]]) !!}

            @include('sendportal::segments.partials.form')

            {!! Form::submitButton(__('Save')) !!}

            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop
