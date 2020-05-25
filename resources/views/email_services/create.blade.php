@extends('sendportal::layouts.app')

@section('heading')
    {{ __('Add Email Service') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Email Service'))

        @slot('cardBody')
            {!! Form::open(['method' => 'post', 'route' => 'sendportal.email_services.store', 'class' => 'form-horizontal']) !!}

            {!! Form::textField('name', __('Name')) !!}
            {!! Form::selectField('type_id', __('Email Service'), $emailServiceTypes) !!}

            <div id="services-fields"></div>

            {!! Form::submitButton(__('Save')) !!}
            {!! Form::close() !!}
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
