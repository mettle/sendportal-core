<div class="main-header">

    <header class="navbar navbar-expand flex-row justify-content-between pl-4-half pr-4-half py-3 mb-4">

        <button type="button" class="btn btn-light mr-3 btn-sm d-xl-none" data-toggle="modal" data-target="#sidebar-modal">
            <i class="fa fa-bars"></i>
        </button>

        <h1 class="h3 mb-0">@yield('heading')</h1>

        {!! \Sendportal\Base\Facades\Sendportal::headerHtmlContent() !!}

    </header>
</div>

