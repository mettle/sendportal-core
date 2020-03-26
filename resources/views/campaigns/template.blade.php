@extends('sendportal::layouts.app')

@section('title', __('Campaign Template'))

@section('heading')
    {{ __('Campaign Template') }}
@stop

@section('content')

    {!! Form::model($campaign, ['id' => 'form-template-selector', 'method' => 'put', 'route' => ['campaigns.template.update', $campaign->id]]) !!}

    <input type="hidden" id="field-template_id" name="template_id" value="{{ $campaign->template_id }}">

    <div class="row">
        @foreach($templates as $template)
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6 template-item">
                <div class="card">
                    <div class="card-header card-header-accent">
                        <div class="card-header-inner">
                            <div class="float-left">
                                <h4>{{ $template->name }}</h4>
                            </div>
                            <div class="float-right">
                                @if ($campaign->template_id == $template->id)
                                    <span class="label label-success">{{ __('Selected') }}</span>
                                @else
                                    <a href="#" class="btn btn-secondary btn-xs js-select-template" data-template_id="{{ $template->id }}">{{ __('Select') }}</a>
                                @endif
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('sendportal::templates.partials.griditem')
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $templates->links() }}

    <a href="{{ route('sendportal.campaigns.edit', $campaign->id) }}" class="btn btn-link"><i
            class="fa fa-arrow-left"></i> {{ __('Back') }}</a>

    <button class="btn btn-primary" type="submit">{{ __('Save and continue') }}</button>

    {!! Form::close() !!}

@stop

@push('js')
    <script>
        $(function () {
            $('.js-select-template').click(function (e) {
                alert('what');
                e.preventDefault();
                $('#field-template_id').val($(this).data('template_id'));
                $('#form-template-selector').submit();
            });
        });
    </script>
@endpush
