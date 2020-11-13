<div {{ $attributes->merge(['class' => 'form-group row form-group-' . $name . ' ' . $wrapperClass  . ' '. $errorClass($name)]) }}>
    <x-sendportal.label :name="$name">{{ $label }}</x-sendportal.label>
    <div class="col-sm-9">
        {{ $slot }}
    </div>
</div>