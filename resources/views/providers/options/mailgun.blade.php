{!! Form::textField('settings[key]', __('API Key'), array_get($settings ?? [], 'key')) !!}
{!! Form::textField('settings[domain]', __('Domain'), array_get($settings ?? [], 'domain')) !!}
{!! Form::selectField('settings[zone]', __('Zone'), ['EU' => 'EU', 'US' => 'US'], array_get($settings ?? [], 'zone')) !!}
