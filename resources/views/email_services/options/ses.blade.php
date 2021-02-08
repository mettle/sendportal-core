<x-sendportal.text-field name="settings[key]" :label="__('AWS Access Key')" :value="Arr::get($settings ?? [], 'key')" autocomplete="off" />
<x-sendportal.text-field name="settings[secret]" :label="__('AWS Secret Access Key')" :value="Arr::get($settings ?? [], 'secret')" autocomplete="off" />
<x-sendportal.text-field name="settings[region]" :label="__('AWS Region')" :value="Arr::get($settings ?? [], 'region')" />
<x-sendportal.text-field name="settings[configuration_set_name]" :label="__('Configuration Set Name')" :value="Arr::get($settings ?? [], 'configuration_set_name')" />