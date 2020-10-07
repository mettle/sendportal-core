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
            {!! Form::model($template, ['method' => 'put', 'route' => ['sendportal.templates.update', $template->id], 'id' => 'template-form', 'class' => 'form-horizontal']) !!}

                <div class="form-group row form-group-name">
                    <label for="id-field-name" class="control-label col-sm-2">{{ __('Template Name') }}</label>
                    <div class="col-sm-6">
                        <input id="id-field-name" class="form-control" name="name" type="text" value="{{ $template->name }}" required>
                    </div>
                </div>

                @if ($template->json)
                    @include('sendportal::templates.partials.unlayer')

                    <div class="form-group row">
                        <input class="btn btn-primary btn-md" type="submit" name="builder" value="{{ __('Save Template') }}" />
                    </div>
                @else
                    @include('sendportal::templates.partials.editor')

                    <div class="form-group row">
                        <div class="col-12">
                            <a href="#" class="btn btn-md btn-secondary btn-preview">{{ __('Show Preview') }}</a>
                            <input class="btn btn-primary btn-md" type="submit" name="raw" value="{{ __('Save Template') }}" />
                        </div>
                    </div>
                @endif

            {!! Form::close() !!}
        </div>
    </div>



@stop
