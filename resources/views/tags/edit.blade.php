@extends('sendportal::layouts.app')

@section('title', __("Edit Segment"))

@section('heading')
    {{ __('Segments') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Edit Segment'))

        @slot('cardBody')
            <form action="{{ route('sendportal.segments.update', $segment->id) }}" method="POST" class="form-horizontal">
                @csrf
                @method('PUT')

                @include('sendportal::segments.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
