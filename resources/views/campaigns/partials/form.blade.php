{!! Form::textField('name', __('Campaign Name')) !!}
{!! Form::textField('subject', __('Email Subject')) !!}
{!! Form::textField('from_name', __('From Name')) !!}
{!! Form::textField('from_email', __('From Email')) !!}
{!! Form::selectField('template_id', __('Template'), $templates, $campaign->template_id ?? null) !!}

@if ($providers->count() === 1)
    {!! Form::hidden('provider_id', $providers->first()->id) !!}
@else
    {!! Form::selectField('provider_id', __('Provider'), $providers->pluck('name', 'id'), isset($campaign->provider_id) ? $campaign->provider_id : null) !!}
@endif

{!! Form::checkboxField('is_open_tracking', __('Track Opens'), 1, $campaign->is_open_tracking ?? 1) !!}
{!! Form::checkboxField('is_click_tracking', __('Track Clicks'), 1, $campaign->is_click_tracking ?? 1) !!}

{!! Form::textareaField('content', __('Content')) !!}

<div class="form-group row">
    <div class="offset-sm-3 col-sm-9">
        <a href="{{ route('campaigns.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary">{{ __('Save and continue') }}</button>
    </div>
</div>

{!! Form::close() !!}

@include('sendportal::layouts.partials.summernote')
