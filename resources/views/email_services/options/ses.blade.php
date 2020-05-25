{!! Form::textField('settings[key]', __('AWS Access Key'), \Arr::get($settings ?? [], 'key'), ['autocomplete' => 'off']) !!}
{!! Form::textField('settings[secret]', __('AWS Secret Access Key'), \Arr::get($settings ?? [], 'secret'), ['autocomplete' => 'off']) !!}
{!! Form::textField('settings[region]', __('AWS Region'), \Arr::get($settings ?? [], 'region')) !!}
{!! Form::textField('settings[configuration_set_name]', __('Configuration Set Name'), \Arr::get($settings ?? [], 'configuration_set_name')) !!}