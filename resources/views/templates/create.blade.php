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

            {!! Form::open(['route' => ['sendportal.templates.store'], 'class' => 'form-horizontal', 'id' => 'template-form']) !!}

                <div class="form-group row form-group-name">
                    <label for="id-field-name" class="control-label col-sm-2">{{ __('Template Name') }}</label>
                    <div class="col-sm-6">
                        <input id="id-field-name" class="form-control" name="name" type="text" value="{{ old('name') }}" required>
                    </div>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="builder-tab" data-toggle="tab" href="#builder" role="tab">{{ __('Builder') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="raw-tab" data-toggle="tab" href="#raw" role="tab">{{ __('Raw HTML') }}</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="builder" role="tabpanel">
                        @include('sendportal::templates.partials.unlayer')

                        <div class="form-group row">
                            <input type="submit" name="builder" value="{{ __('Save Template') }}" class="btn btn-primary btn-md">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="raw" role="tabpanel">
                        @include('sendportal::templates.partials.editor')

                        <div class="form-group row">
                            <div class="col-12">
                                <a href="#" class="btn btn-md btn-secondary btn-preview">{{ __('Show Preview') }}</a>
                                <input type="submit" name="raw" value="{{ __('Save Template') }}" class="btn btn-primary btn-md">
                            </div>
                        </div>
                    </div>
                </div>

            {!! Form::close() !!}
        </div>
    </div>

@stop