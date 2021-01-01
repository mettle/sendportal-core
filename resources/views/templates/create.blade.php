@extends('sendportal::layouts.app')

@section('title', __('New Template'))

@section('heading')
    {{ __('Templates') }}
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            {{ __('Create Template') }}
        </div>
        <div class="card-body">
            <form action="{{ route('sendportal.templates.store') }}" method="POST" class="form-horizontal">
                @csrf
                @include('sendportal::templates.partials.form')
            </form>
        </div>
    </div>

@stop
