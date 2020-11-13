<x-sendportal.field-wrapper :name="$name" :label="$label">
    <select name="{{ $name }}" {{ $attributes->merge(['id' => 'id-field-' .  str_replace('[]', '', $name), 'class' => 'form-control ' . ($multiple ? 'selectpicker' : ''), 'multiple' => $multiple]) }}>
        @foreach($options as $key => $text)
            <option value="{{ $key }}" {{ $isSelected($key) ? 'selected' : '' }}>{{ $text }}</option>
        @endforeach
    </select>
</x-sendportal.field-wrapper>
