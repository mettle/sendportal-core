<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>
        @hasSection('title')
            @yield('title') |
        @endif
        {{ config('app.name') }}
    </title>

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fa.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('css')

</head>
<body>

<div class="container-fluid">
    <div class="row">

        @auth()
            <div class="sidebar bg-dark-blue min-vh-100 d-none d-xl-block">

                <div class="mt-4">
                    <div class="logo text-center">
                        <a href="/">
                            <img src="{{ asset('img/logo-blue.png') }}" alt="" width="175px">
                        </a>
                    </div>
                </div>

                <div class="mt-5">
                    @include('sendportal::layouts.partials.sidebar')
                </div>
            </div>
        @endauth()

        @include('sendportal::layouts.main')
    </div>
</div>

<script src="{{ asset('js/jquery-3.3.1.js') }}"></script>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

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
