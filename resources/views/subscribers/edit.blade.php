@extends('sendportal::layouts.app')

@section('title', __("Edit Subscriber") . " : {$subscriber->full_name}")

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Edit Subscriber'))

        @slot('cardBody')
            <form action="{{ route('sendportal.subscribers.update', $subscriber->id) }}" method="POST" class="form-horizontal">
                @csrf
                @method('PUT')

                @include('sendportal::subscribers.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
