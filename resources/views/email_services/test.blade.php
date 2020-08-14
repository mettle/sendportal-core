@extends('sendportal::layouts.app')

@section('heading')
    {{ __('Test Email Service') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Test Email Service') . ' : ' . $emailService->name)

        @slot('cardBody')
            {!! Form::open(['method' => 'POST', 'class' => 'form-horizontal', 'route' => ['sendportal.email_services.test.store', $emailService->id]]) !!}

            {!! Form::textField('to', 'To Email', auth()->user()->email, ['readonly' => 'readonly']) !!}

            <div class="form-group row form-group-email">
                <label for="id-field-email" class="control-label col-sm-3">{{ __('From Email') }}</label>
                <div class="col-sm-9">
                    <input id="id-field-email" class="form-control" name="from" type="email" required>
                    <small class="form-text text-muted">{{ __('Must be a verified :service email address or domain', ['service' => $emailService->type->name]) }}</small>
                </div>
            </div>

            {!! Form::textField('subject', __('Subject'), 'Sendportal Test Email', null, ['required' => 'required']) !!}

            {!! Form::textareaField('body', __('Email Body'), 'This is a test for the email service ' . $emailService->name, ['required' => 'required']) !!}

            {!! Form::submitButton(__('Test')) !!}
            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop


