@extends('sendportal::layouts.app')

@section('title', __('New Segment'))

@section('heading')
    {{ __('Segments') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Segment'))

        @slot('cardBody')
            <form action="{{ route('sendportal.segments.store') }}" method="POST" class="form-horizontal">
                @csrf

                @include('sendportal::segments.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
