<div class="main-wrapper col p-0 min-vh-100">

    @auth()
        <div class="modal modal-left fade sidebar" id="sidebar-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable bg-purple-100 mh-100" role="document">
                <div class="modal-content border-0 rounded-0 mh-100">

                    <div class="modal-body bg-purple-100 p-0">

                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                        <div class="logo text-center mt-4">
                            <a href="/">
                                <img src="{{ asset('/vendor/sendportal/img/logo-main.png') }}" alt="SendPortal" width="130">
                            </a>
                        </div>

                        @include('sendportal::layouts.partials.sidebar')
                    </div>
                </div>
            </div>
        </div>

        @include('sendportal::layouts.partials.header')

    @endauth


    <div class="main-content pl-4-half pr-4-half pb-4-half">

        @if( ! in_array(request()->route()->getName(), [
            'login',
            'register',
            'password.reset',
        ]))
            @include('sendportal::layouts.partials.errors')
        @endif

        @include('sendportal::layouts.partials.success')
        @include('sendportal::layouts.partials.error')

        @yield('content')
    </div>

</div>
