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

                            <x-sendportal.select-field name="coin_id" :label="__('Coin')" :options="$workspaces->pluck('coin', 'id', 'unit_equivalence')" id="coin_value" :value="$workspace->workspace_id ?? old('workspace_id')" />
                            <x-sendportal.text-field id="amount" name="amount" :label="__('Amount')" :value="$workspace->amount ?? old('amount')" />
                            <x-sendportal.text-field name="amount_in_unit" id="units" :label="__('Expected Units')" :value="$workspace->expected_unit ?? old('expected_unit')" readonly  />
                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <a href="{{ route('sendportal.units.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Make Payment') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                   
                </div>
            </div>
        </div>
        @if(isset($transaction_payload))
        <!-- {{dd($transaction_payload)}} -->
            <script>
                function payWithSocialPay(data) {
                    console.log(data);
                    var handler = SocialPay.invoice({
                    public_key: "PUBLIC_KEY_cl8soodjc0003iggmxp5phm2ucl8soodjc0004iggmmucwstpa",
                    order_id: "'"+data.order_id+"'",
                    first_name: "'"+data.first_name+"'",
                    last_name: "'"+data.last_name+"'",
                    email: "'"+data.email+"'",
                    fee_bearer: "'"+data.fee_bearer+"'",
                    amount_charge: "'"+data.amount_charge+"'",
                    cryptocurrency_type: "'"+data.amount_charge+"'",
                    custom_fields: [
                        {
                        website: "",
                        twitter: ""
                        }
                    ],
                    callback: function (response) {
                        $.get("/callback/"+ response.reference_code, function(data, status){
                            window.location.href = './units';
                        });
                        console.log(response);
                    },
                    onClose: function () {
                        console.log("Window Closed.");
                    }
                    });
                
                    handler.openIframe();
                }
                // ($transaction_payload)
            </script>
        @endif
       

@stop
@push('js')
    <script src="{{ asset('vendor/sendportal/js/Chart.bundle.min.js') }}"></script>

    <script>
        $( document ). ready( function(){
            var space = 0
            var workspace = <?=$workspaces?>;
            $("#amount").on('keyup', function(){
                $("#units").val($("#amount").val()*space)
            })

            $("#coin_value").on('change', function(){
                var filteredWorkspace = workspace.filter((ele)=>{
                        return $("#coin_value").val() == ele.id
                    })
                    space = filteredWorkspace[0].unit_equivalence;
                    $("#units").val($("#amount").val()*space)
            })
            
            var filteredWorkspace = workspace.filter((ele)=>{
                return $("#coin_value").val() == ele.id
                })
                space = filteredWorkspace[0].unit_equivalence;
        } )

        
       
    </script>
@endpush