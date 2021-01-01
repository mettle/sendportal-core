<x-sendportal.field-wrapper :name="$name" :label="$label">
    <textarea name="{{ $name }}" {{ $attributes->merge(['id' => 'id-field-' .  str_replace('[]', '', $name), 'class' => 'form-control']) }}>{{ $slot }}</textarea>
</x-sendportal.field-wrapper>
