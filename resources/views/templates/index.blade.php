@extends('sendportal::layouts.app')

@section('title', __('Email Templates'))

@section('heading')
    {{ __('Email Templates') }}
@endsection

@section('content')

    @component('sendportal::layouts.partials.actions')
        @slot('left')
            <form action="{{ route('sendportal.templates.index') }}" method="GET" class="form-inline mb-3 mb-md-0">
                <input class="form-control form-control-sm" name="name" type="text" value="{{ request('name') }}"
                    placeholder="{{ __('Search...') }}">

                <button type="submit" class="btn btn-light btn-md ml-2">{{ __('Search') }}</button>
            </form>
        @endslot

        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.templates.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Template') }}
            </a>
        @endslot
    @endcomponent

    @include('sendportal::templates.partials.grid')

@endsection
