@extends('sendportal::layouts.app')

@section('title', __("Templates"))

@section('heading')

@stop

@section('content')

    <div class="card">
        <div class="card-header">
            {{ __('Edit Template') }}
        </div>
        <div class="card-body">
            {!! Form::model($template, ['method' => 'put', 'route' => ['sendportal.templates.update', $template->id], 'class' => 'form-horizontal']) !!}

            @include('sendportal::templates.partials.form')
        </div>
    </div>



@stop
