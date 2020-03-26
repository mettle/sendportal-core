@extends('sendportal::layouts.app')

@section('heading')
    {{ __('Add Email Provider') }}
@stop

@section('content')

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Create Provider'))

        @slot('cardBody')
            {!! Form::open(['method' => 'post', 'route' => 'providers.store', 'class' => 'form-horizontal']) !!}

            {!! Form::textField('name', __('Name')) !!}
            {!! Form::selectField('type_id', __('Provider'), $providerTypes) !!}

            <div id="provider-fields"></div>

            {!! Form::submitButton(__('Save')) !!}
            {!! Form::close() !!}
        @endSlot
    @endcomponent

@stop

@push('js')
    <script>

        let url = '{{ route('providers.ajax', 1) }}';
        let old = {!! json_encode(old()) !!};

        $(function() {
            let type_id = $('select[name="type_id"]').val();

            createFields(type_id);

            $('#id-field-type_id').on('change', function() {
                createFields(this.value);
            });
        });

        function createFields(providerTypeId)
        {
            url = url.substring(0, url.length - 1) + providerTypeId;

            $.get(url, function(result) {
                $('#provider-fields')
                  .html('')
                  .append(result.view);
            });
        }

    </script>
@endpush
