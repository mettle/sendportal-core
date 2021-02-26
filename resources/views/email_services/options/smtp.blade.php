<x-sendportal.text-field name="settings[host]" :label="__('SMTP Host')" :value="Arr::get($settings ?? [], 'host')" />
<x-sendportal.text-field type="number" name="settings[port]" :label="__('SMTP Port')" :value="Arr::get($settings ?? [], 'port')" />
<x-sendportal.text-field name="settings[encryption]" :label="__('Encryption')" :value="Arr::get($settings ?? [], 'encryption')" />
<x-sendportal.text-field name="settings[username]" :label="__('Username')" :value="Arr::get($settings ?? [], 'username')" />
<x-sendportal.text-field type="password" name="settings[password]" :label="__('Password')" :value="Arr::get($settings ?? [], 'password')" />