@extends('sendportal::layouts.app')

@section('title', __('Load Unit'))

@section('heading', __('User Units'))

@section('content')

   
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card">
                    <div class="card-header">
                        {{ __('Load Unit') }}
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sendportal.units.initializeTransaction') }}" method="POST" class="form-horizontal">
                            @csrf
                            <!-- <x-sendportal.text-field name="name" :label="__('Campaign Name')" :value="$campaign->name ?? old('name')" />
                            <x-sendportal.text-field name="subject" :label="__('Email Subject')" :value="$campaign->subject ?? old('subject')" /> -->
                           
                            {{--<x-sendportal.text-field name="from_email" :label="__('From Email')" type="email" :value="$campaign->from_email ?? old('from_email')" />--}}

                            <x-sendportal.select-field name="workspace_id" :label="__('Coin')" :options="$workspaces->pluck('name', 'id')" :value="$workspace->workspace_id ?? old('workspace_id')" />
                            <x-sendportal.text-field id="amount" name="amount" :label="__('Amount')" :value="$workspace->amount ?? old('amount')" />
                            <x-sendportal.text-field name="amount_in_unit" id="units" :label="__('Expected Units')" :value="$workspace->expected_unit ?? old('expected_unit')" readonly  />
                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <a href="{{ route('sendportal.units.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Save and continue') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
       

@stop
@push('js')
    <script src="{{ asset('vendor/sendportal/js/Chart.bundle.min.js') }}"></script>

    <script>
        $( document ). ready( function(){

            $("#amount").on('keyup', function(){
                $("#units").val($("#amount").val()*10)
            })
        } )
       
    </script>
@endpush