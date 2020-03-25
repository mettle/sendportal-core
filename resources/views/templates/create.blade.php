@extends('layouts.app')

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
            {!! Form::open(['route' => ['templates.store'], 'class' => 'form-horizontal']) !!}

            @include('templates.partials.form')
        </div>
    </div>

@stop
