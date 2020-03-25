@extends('sendportal::layouts.app')

@section('title', __('Email Templates'))

@section('heading')
    {{ __('Email Templates') }}
@endsection

@section('content')

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('templates.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Template') }}
            </a>
        @endslot
    @endcomponent

    @include('templates.partials.grid')

@endsection
