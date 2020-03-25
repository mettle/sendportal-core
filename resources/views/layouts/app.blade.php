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

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link href="{{ asset(mix('app.css', 'vendor/sendportal')) }}" rel="stylesheet">

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
                            <img src="{{ asset('/vendor/sendportal/img/logo-blue.png') }}" alt="" width="175px">
                        </a>
                    </div>
                </div>

                <div class="mt-5">
                    @include('layouts.partials.sidebar')
                </div>
            </div>
        @endauth()

        @include('layouts.main')
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

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
