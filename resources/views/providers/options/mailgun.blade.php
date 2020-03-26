{!! Form::textField('settings[key]', __('API Key'), \Arr::get($settings ?? [], 'key')) !!}
{!! Form::textField('settings[domain]', __('Domain'), \Arr::get($settings ?? [], 'domain')) !!}
{!! Form::selectField('settings[zone]', __('Zone'), ['EU' => 'EU', 'US' => 'US'], \Arr::get($settings ?? [], 'zone')) !!}
