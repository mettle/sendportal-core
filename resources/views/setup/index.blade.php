@extends('sendportal::layouts.app')

@section('title', 'Application Setup')

@push('css')
    @livewireStyles
@endpush

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                @include('sendportal::auth.partials.logo')

                <livewire:setup />
            </div>
        </div>
    </div>

@endsection

@push('js')
    @livewireScripts
@endpush