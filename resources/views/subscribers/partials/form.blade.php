<x-sendportal.text-field name="email" :label="__('Email')" type="email" :value="$subscriber->email ?? null" />
<x-sendportal.text-field name="first_name" :label="__('First Name')" :value="$subscriber->first_name ?? null" />
<x-sendportal.text-field name="last_name" :label="__('Last Name')" :value="$subscriber->last_name ?? null" />
<x-sendportal.select-field name="tags[]" :label="__('Tags')" :options="$tags" :value="$selectedTags" multiple />
<x-sendportal.checkbox-field name="subscribed" :label="__('Subscribed')" :checked="empty($subscriber->unsubscribed_at)" />

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.12/dist/css/bootstrap-select.min.css">
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.12/dist/js/bootstrap-select.min.js"></script>
@endpush
