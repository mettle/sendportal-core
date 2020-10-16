@extends('sendportal::layouts.base')

@section('title', 'Application Setup')

@push('css')
    @livewireStyles
@endpush

@section('htmlBody')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="logo text-center">
                    <img src="{{ asset('/vendor/sendportal/img/logo-gray.png') }}" alt="SendPortal" width="225px" class="my-5">
                </div>

                <livewire:setup />
            </div>
        </div>
    </div>

@endsection

@push('js')
    @livewireScripts
@endpush