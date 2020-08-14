{!! Form::textField('settings[key]', __('Mailjet Key'), \Arr::get($settings ?? [], 'key'), ['autocomplete' => 'off']) !!}
{!! Form::textField('settings[secret]', __('Mailjet Secret'), \Arr::get($settings ?? [], 'secret'), ['autocomplete' => 'off']) !!}
{!! Form::selectField('settings[zone]', __('Zone'), ['Default' => 'Default', 'US' => 'US'], \Arr::get($settings ?? [], 'zone')) !!}