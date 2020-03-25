{!! Form::textField('settings[key]', __('AWS Access Key'), array_get($settings ?? [], 'key')) !!}
{!! Form::textField('settings[secret]', __('AWS Secret Access Key'), array_get($settings ?? [], 'secret')) !!}
{!! Form::textField('settings[region]', __('AWS Region'), array_get($settings ?? [], 'region')) !!}
{!! Form::textField('settings[configuration_set_name]', __('Configuration Set Name'), array_get($settings ?? [], 'configuration_set_name')) !!}