{!! Form::textField('email', __('Email')) !!}
{!! Form::textField('first_name', __('First Name')) !!}
{!! Form::textField('last_name', __('Last Name')) !!}
{!! Form::selectMultipleField('segments[]', __('Segments'), $segments, $selectedSegments) !!}
{!! Form::checkboxField('subscribed', __('Subscribed'), 1, empty($subscriber->unsubscribed_at)) !!}

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.12/dist/css/bootstrap-select.min.css">
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.12/dist/js/bootstrap-select.min.js"></script>
@endpush
