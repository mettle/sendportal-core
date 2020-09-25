@extends('sendportal::layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css">
@endpush

@section('heading')
    {{ __('Import Subscribers') }}
@stop

@section('content')

    @if (isset($errors) and count($errors->getBags()))
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->getBags() as $key => $bag)
                            @foreach($bag->all() as $error)
                                <li>{{ $key }} - {{ $error }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @component('sendportal::layouts.partials.card')
        @slot('cardHeader', __('Import via CSV file'))

        @slot('cardBody')
            <p><b>{{ __('CSV format') }}:</b> {{ __('Format your CSV the same way as the example below (with the first title row). Use the ID or email columns if you want to update a Subscriber instead of creating it.') }}</p>

            <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('id') }}</th>
                            <th>{{ __('email') }}</th>
                            <th>{{ __('first_name') }}</th>
                            <th>{{ __('last_name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td>me@sendportal.io</td>
                            <td>Myself</td>
                            <td>Included</td>
                        </tr>
                    </tbody>
                </table>
            </div>


            {!! Form::open(['route' => ['sendportal.subscribers.import.store'], 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}

            {!! Form::fileField('file', 'File', ['required' => 'required']) !!}

            <div class="form-group row form-group-subscribers">
                <label for="id-field-subscribers" class="control-label col-sm-3">{{ __('Segments') }}</label>
                <div class="col-sm-9">
                    {!! Form::select('segments[]', $segments, null, ['multiple' => true]) !!}
                </div>
            </div>

            {!! Form::checkboxField('validate', __('Do not import any row if the file contains errors'))!!}

            <div class="form-group row">
                <div class="offset-sm-3 col-sm-9">
                    <a href="{{ route('sendportal.subscribers.index') }}" class="btn btn-light">{{ __('Back') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                </div>
            </div>

            {!! Form::close() !!}

        @endSlot
    @endcomponent

@stop

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"></script>

    <script>
        $('select[name="segments[]"]').selectize({
            plugins: ['remove_button']
        });
    </script>
@endpush