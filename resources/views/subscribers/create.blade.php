@extends('sendportal::layouts.app')

@section('title', __('New Subscriber'))

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Subscriber'))

        @slot('cardBody')
            <form action="{{ route('sendportal.subscribers.store') }}" class="form-horizontal" method="POST">
                @csrf
                @include('sendportal::subscribers.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
