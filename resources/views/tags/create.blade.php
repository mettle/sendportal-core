@extends('sendportal::layouts.app')

@section('title', __('New Tag'))

@section('heading')
    {{ __('Tags') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Tag'))

        @slot('cardBody')
            <form action="{{ route('sendportal.tags.store') }}" method="POST" class="form-horizontal">
                @csrf

                @include('sendportal::tags.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
