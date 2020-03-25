@extends('sendportal::layouts.app')

@section('title', __('Create Campaign'))

@section('heading', __('Campaigns'))

@section('content')

	@if( ! $providers)
        <div class="callout callout-danger">
            <h4>{{ __('You haven\'t added any providers!') }}</h4>
            <p>{{ __('Before you can create a campaign, you must first') }} <a href="{{ route('providers.create') }}">{{ __('add a provider') }}</a>.
            </p>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card">
                    <div class="card-header">
                        {{ __('Create Campaign') }}
                    </div>
                    <div class="card-body">
                        {!! Form::open(['route' => ['campaigns.store'], 'class' => 'form-horizontal']) !!}

                        @include('sendportal::campaigns.partials.form')
                    </div>
                </div>
            </div>
        </div>
	@endif
@stop
