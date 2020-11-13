@extends('sendportal::layouts.app')

@section('heading')
    {{ __('Add Email Service') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Email Service'))

        @slot('cardBody')
            <form action="{{ route('sendportal.email_services.store') }}" method="POST" class="form-horizontal">
                @csrf
                <x-sendportal.text-field name="name" :label="__('Name')" />
                <x-sendportal.select-field name="type_id" :label="__('Email Service')" :options="$emailServiceTypes" />

                <div id="services-fields"></div>

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop

@push('js')
    <script>

        let url = '{{ route('sendportal.email_services.ajax', 1) }}';

        $(function () {
            let type_id = $('select[name="type_id"]').val();

            createFields(type_id);

            $('#id-field-type_id').on('change', function () {
                createFields(this.value);
            });
        });

        function createFields(serviceTypeId) {
            url = url.substring(0, url.length - 1) + serviceTypeId;

            $.get(url, function (result) {
                $('#services-fields')
                  .html('')
                  .append(result.view);
            });
        }

    </script>
@endpush
