@extends('sendportal::layouts.app')

@section('title', __('Edit Campaign'))

@section('heading')
    {{ __('Edit Campaign') }}
@stop

@section('content')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    {{ __('Edit Campaign') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('sendportal.campaigns.update', $campaign->id) }}" method="POST" class="form-horizontal">
                        @csrf
                        @method('PUT')
                        @include('sendportal::campaigns.partials.form')
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
