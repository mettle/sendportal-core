<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="mt-5">
        @include('layouts.partials.success')

        @yield('content')
    </div>
</div>

</body>
</html>
