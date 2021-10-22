<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    @include('sendportal::layouts.partials.favicons')

    <title>
        @hasSection('title')
            @yield('title') |
        @endif
        {{ config('app.name') }}
    </title>

    <link href="{{ asset('vendor/sendportal/css/fontawesome-all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/sendportal/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset(mix('app.css', 'vendor/sendportal')) }}" rel="stylesheet">

    @stack('css')

</head>
<body>

@yield('htmlBody')

<script src="{{ asset('vendor/sendportal/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('vendor/sendportal/js/popper.min.js') }}"></script>
<script src="{{ asset('vendor/sendportal/js/bootstrap.min.js') }}"></script>

<script>
    $('.sidebar-toggle').click(function (e) {
        e.preventDefault();
        toggleElements();
    });

    function toggleElements() {
        $('.sidebar').toggleClass('d-none');
    }
</script>

@stack('js')

</body>
</html>
