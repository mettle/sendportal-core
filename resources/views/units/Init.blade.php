@extends('sendportal::layouts.app')

@section('title', __('Load Credits'))

@section('heading', __('User Credits'))

@section('content')


           
       

@stop
@push('js')
    <script src="{{ asset('vendor/sendportal/js/Chart.bundle.min.js') }}"></script>

    <script>
        
                function payWithSocialPay(data) {
                    var handler = SocialPay.invoice({
                    public_key: "PUBLIC_KEY_cl8soodjc0003iggmxp5phm2ucl8soodjc0004iggmmucwstpa",
                    order_id: data.order_id,
                    first_name: data.first_name,
                    last_name: data.last_name,
                    email: data.email,
                    fee_bearer: data.fee_bearer,
                    amount_charge: data.amount_charge,
                    cryptocurrency_type: data.cryptocurrency_type,
                    custom_fields: [
                        {
                        website: "",
                        twitter: ""
                        }
                    ],
                    callback: function (response) {
                        $.get("/sendportal/callback/"+ response.reference_code, function(data, status){
                            window.location.href = './units';
                        });
                    },
                    onClose: function () {
                        $.get("/sendportal/callback/7h3YUz84LNs4gxP9vwSNBZiFiVPDTRQfBUCjR3KbD8gk", function(data, status){
                            window.location.href = './units';
                        });
                    }
                    });
                
                    handler.openIframe();
                }
                payWithSocialPay(<?=json_encode($transaction_payload)?>)
            </script>
@endpush