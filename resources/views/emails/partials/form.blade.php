{!! Form::textField('subject', __('Email Subject'), optional($campaign->email)->subject) !!}
{!! Form::textField('from_name', __('From Name'), optional($campaign->email)->from_name) !!}
{!! Form::textField('from_email', __('From Email'), optional($campaign->email)->from_email) !!}
{!! Form::selectField('template_id', __('Templates'), $templates) !!}
